<?php
namespace Modules\Booking\Gateways;
use App\Currency;
use Illuminate\Http\Request;
use Mockery\Exception;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\Payment;
use Omnipay\Omnipay;
use Omnipay\PayPal\ExpressGateway;
use Illuminate\Support\Facades\Log;
class YookassaGateway extends BaseGateway
{
    public $name = 'Yookassa';
    /**
     * @var $gateway ExpressGateway
     */
    protected $gateway;

    public function getOptionsConfigs()
    {
        return [
            [
                'type'  => 'checkbox',
                'id'    => 'enable',
                'label' => __('Enable Yookassa Standard?')
            ],            
            [
                'type'       => 'input',
                'id'         => 'name',
                'label'      => __('Custom Name'),
                'std'        => __("Yookassa"),
                'multi_lang' => "1"
            ],
            [
                'type'  => 'upload',
                'id'    => 'logo_id',
                'label' => __('Custom Logo'),
            ],
            [
                'type'  => 'editor',
                'id'    => 'html',
                'label' => __('Custom HTML Description'),
                'multi_lang' => "1"
            ],
            [
                'type'    => 'select',
                'id'      => 'convert_to',
                'label'   => __('Convert To'),
                'desc'    => __('In case of main currency does not support by PayPal. You must select currency and input exchange_rate to currency that PayPal support'),
                'options' => $this->supportedCurrency()
            ],
            [
                'type'       => 'input',
                'input_type' => 'number',
                'id'         => 'exchange_rate',
                'label'      => __('Exchange Rate'),
                'desc'       => __('Example: Main currency is VND (which does not support by PayPal), you may want to convert it to USD when customer checkout, so the exchange rate must be 23400 (1 USD ~ 23400 VND)'),
            ],
            [
                'type'      => 'input',
                'id'        => 'client_id',
                'label'     => __('ShopID')
            ],
            [
                'type'      => 'input',
                'id'        => 'client_secret',
                'label'     => __('Secret Key'),
                'std'       => ''
            ],
        ];
    }

    public function process(Request $request, $booking, $service)
    {
        if (in_array($booking->status, [
            $booking::PAID,
            $booking::COMPLETED,
            $booking::CANCELLED
        ])) {

            throw new Exception(__("Booking status does need to be paid"));
        }
        if (!$booking->pay_now) {
            throw new Exception(__("Booking total is zero. Can not process payment gateway!"));
        }
        $payment = new Payment();
        $payment->booking_id = $booking->id;
        $payment->payment_gateway = $this->id;
        $payment->status = 'draft';
        $data = $this->handlePurchaseData([
            'amount'        => (float)$booking->pay_now,
            'transactionId' => $booking->code . '.' . time()
        ], $booking, $payment);
            $params = array(
                'amount' => array(
                    'value' => (float)$booking->pay_now,
                    'currency' => 'RUB',
                ),
                'confirmation' => array(
                    'type' => 'redirect',
                    'return_url' => 'https://locotrips.ru/user/booking-history',
                ),
                'capture' => true,
                'description' => 'Бронирование №'.$booking->id,
                'metadata' => array(
                    'order_id' => $booking->id,
                ),
                'receipt' => array(
                    'customer' => array(
                        'email' => $booking->email,
                        'full_name' => $booking->first_name.' '.$booking->last_name
                    ),
                    'items' => array(
					    array(
					        'description' => 'Бронирование №'.$booking->id,
					        'quantity'    => 1,
					        "amount" => array(
                                "value" => (float)$booking->pay_now,
                                "currency" => "RUB"
                            ),
					        'vat_code' => '1',
					        'payment_mode' => 'partial_prepayment',
					        'payment_subject' => 'service',
                        )
                    )
                )
            );
            $ch = curl_init('https://api.yookassa.ru/v3/payments');
            curl_setopt($ch, CURLOPT_USERPWD, $this->getOption('client_id').':'.$this->getOption('client_secret'));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json ',
                'Idempotence-Key: '.md5(time().'yookassa'.$this->getOption('client_id'))
            ));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            $r = curl_exec($ch);
            $i = curl_getinfo($ch);
            curl_close($ch);
            $result = json_decode($r, true);
            $payment->save();
            $booking->status = $booking::UNPAID;
            $booking->payment_id = $payment->id;
            $booking->save();
            response()->json([
                'url' => $result['confirmation']['confirmation_url']
            ])->send();
    }

    public function confirmPayment(Request $request)
    {

    }

    public function confirmNormalPayment()
    {

    }

    public function callbackPayment(Request $request){
        app('debugbar')->disable();
        $source = file_get_contents('php://input');
        $data = json_decode($source, true);
        if($data != null){
            $booking = Booking::where('id', $data['object']['metadata']['order_id'])->first();
            if (!empty($booking) and in_array($booking->status, [$booking::UNPAID])) {
                if ($data['event'] != 'payment.succeeded') {
                    $payment = $booking->payment;
                    if ($payment) {
                        $payment->status = 'fail';
                        $payment->logs = \GuzzleHttp\json_encode($request->input());
                        $payment->save();
                    }
                    try {
                        $booking->markAsPaymentFailed();
                    } catch (\Swift_TransportException $e) {
                        Log::warning($e->getMessage());
                    }
                } else {
                    $payment = $booking->payment;
                    if ($payment) {
                        $payment->status = 'completed';
                        $payment->logs = \GuzzleHttp\json_encode($request->input());
                        $payment->save();
                    }
                    try {
                        $booking->paid += (float)$booking->pay_now;
                        $booking->markAsPaid();
                    } catch (\Swift_TransportException $e) {
                        Log::warning($e->getMessage());
                    }
                }
            }
        }else{
            die('Hacking attempt');
        }
    }

    public function processNormal($payment)
    {

    }

    public function cancelPayment(Request $request)
    {
        $c = $request->query('c');
        $booking = Booking::where('code', $c)->first();
        if (!empty($booking) and in_array($booking->status, [$booking::PAID])) {
            $payment = $booking->payment;
            $info = json_decode($payment->logs, true);
            $params = array(
                'amount' => array(
                    'value' => (float)$info['object']['amount']['value'],
                    'currency' => 'RUB',
                ),
                'payment_id' => $info['object']['id']
            );
            $ch = curl_init('https://api.yookassa.ru/v3/refunds');
            curl_setopt($ch, CURLOPT_USERPWD, $this->getOption('client_id').':'.$this->getOption('client_secret'));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json ',
                'Idempotence-Key: '.md5(time().'yookassa'.$this->getOption('client_id'))
            ));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            $r = curl_exec($ch);
            $i = curl_getinfo($ch);
            curl_close($ch);
            $result = json_decode($r, true);
            if($result['status'] == 'succeeded'){
                if ($payment) {
                    $payment->status = 'cancel';
                    $payment->logs = \GuzzleHttp\json_encode([
                        'customer_cancel' => 1
                    ]);
                    $payment->save();
                }
                return redirect($booking->getDetailUrl())->with("error", __("You cancelled the payment"));
            }else{
                return redirect($booking->getDetailUrl())->with("error", "Не получилось отменить платеж");
            }
        }
        if (!empty($booking)) {
           return redirect($booking->getDetailUrl());
        } else {
            return redirect(url('/'));
        }
    }


    public function handlePurchaseDataNormal($data, &$payment = null)
    {
        $main_currency = setting_item('currency_main');
        $supported = $this->supportedCurrency();
        $convert_to = $this->getOption('convert_to');
        $data['currency'] = $main_currency;
        $data['returnUrl'] = $this->getReturnUrl(true) . '?pid=' . $payment->code;
        $data['cancelUrl'] = $this->getCancelUrl(true) . '?pid=' . $payment->code;
        if (!array_key_exists($main_currency, $supported)) {
            if (!$convert_to) {
                throw new Exception(__("PayPal does not support currency: :name", ['name' => $main_currency]));
            }
            if (!$exchange_rate = $this->getOption('exchange_rate')) {
                throw new Exception(__("Exchange rate to :name must be specific. Please contact site owner", ['name' => $convert_to]));
            }
            if ($payment) {
                $payment->converted_currency = $convert_to;
                $payment->converted_amount = $payment->amount / $exchange_rate;
                $payment->exchange_rate = $exchange_rate;
                $payment->save();
            }
            $data['amount'] = number_format( $payment->amount / $exchange_rate , 2 );
            $data['currency'] = $convert_to;
        }
        return $data;
    }
    public function handlePurchaseData($data, $booking, &$payment = null)
    {
        $main_currency = setting_item('currency_main');
        $supported = $this->supportedCurrency();
        $convert_to = $this->getOption('convert_to');
        $data['currency'] = $main_currency;
        $data['returnUrl'] = $this->getReturnUrl() . '?c=' . $booking->code;
        $data['cancelUrl'] = $this->getCancelUrl() . '?c=' . $booking->code;
        if (!array_key_exists($main_currency, $supported)) {
            if (!$convert_to) {
                throw new Exception(__("PayPal does not support currency: :name", ['name' => $main_currency]));
            }
            if (!$exchange_rate = $this->getOption('exchange_rate')) {
                throw new Exception(__("Exchange rate to :name must be specific. Please contact site owner", ['name' => $convert_to]));
            }
            if ($payment) {
                $payment->converted_currency = $convert_to;
                $payment->converted_amount = $booking->pay_now / $exchange_rate;
                $payment->exchange_rate = $exchange_rate;
            }
            $data['originalAmount'] = (float)$booking->pay_now;
            $data['amount'] = number_format( (float)$booking->pay_now / $exchange_rate , 2 );
            $data['currency'] = $convert_to;
        }
        return $data;
    }

    public function supportedCurrency()
    {
        return [
            "rub" => "Russian ruble",
        ];
    }
}
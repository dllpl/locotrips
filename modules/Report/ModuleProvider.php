<?php


namespace Modules\Report;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\User\Models\Wallet\DepositPayment;

class ModuleProvider extends \Modules\ModuleServiceProvider
{
    public function register()
    {

        $this->app->register(RouteServiceProvider::class);
    }
    public static function getAdminMenu()
    {
        $count = 0;
        $pending_purchase = DepositPayment::countPending();
        $contact_count = DB::table('bravo_contact')->whereDate('created_at', Carbon::today())->count();

        $count += $pending_purchase;
        return [
            'report'=>[
                "position"=>110,
                'url'        => route('report.admin.booking'),
                'title'      =>  __('Reports :count',['count'=>$contact_count ? sprintf('<span class="badge badge-warning">%d</span>',$contact_count) : '']),
                'icon'       => 'icon ion-ios-pie',
                'permission' => 'report_view',
                'children'   => [
                    'enquiry'=>[
                        'url'        => route('report.admin.enquiry.index'),
                        'title'      => __('Enquiry Reports'),
                        'icon'       => 'icon ion-ios-pricetags',
                        'permission' => 'report_view',
                    ],
                    'booking'=>[
                        'url'        => route('report.admin.booking'),
                        'title'      => __('Booking Reports'),
                        'icon'       => 'icon ion-ios-pricetags',
                        'permission' => 'report_view',
                    ],
                    'statistic'=>[
                        'url'        => route('report.admin.statistic.index'),
                        'title'      => __('Booking Statistic'),
                        'icon'       => 'icon ion ion-md-podium',
                        'permission' => 'report_view',
                    ],
                    'contact'=>[
                        'url'        => route('contact.admin.index'),
                        'title'      => __('Обратная связь :count', ['count'=>$contact_count ? sprintf('<span class="badge badge-warning">%d</span>',$contact_count) : '']),
                        'icon'       => 'icon ion ion-md-mail',
                        'permission' => 'contact_manage',
                    ],
                    'buy_credit_report'=>[
                        'parent'=>'report',
                        'url'=>route('user.admin.wallet.report'),
                        'title'=>__("Credit Purchase Report :count",['count'=>$pending_purchase ? sprintf('<span class="badge badge-warning">%d</span>',$pending_purchase) : '']),
                        'icon'=>'fa fa-money'
                    ]
                ]
            ],
        ];
    }
}

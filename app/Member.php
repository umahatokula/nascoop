<?php

namespace App;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

use App\Share;
use Carbon\Carbon;

class Member extends Model
{
    use HasSlug, SearchableTrait;

    protected $table = 'members';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $casts = [ 
        // 'is_active'      => 'boolean',
        'savings_locked' => 'boolean',
    ];
    
    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('full_name')
            ->saveSlugsTo('slug');
    }

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchable = [
        /**
         * Columns and their priority in search results.
         * Columns with higher values are more important.
         * Columns with equal values have equal importance.
         *
         * @var array
         */
        'columns' => [
            'members.full_name' => 5,
            'members.ippis' => 10,
        ],
        // 'joins' => [
        //     'posts' => ['users.id','posts.user_id'],
        // ],
    ];


    public function user()
    {
        return $this->hasOne(User::class, 'ippis', 'ippis');
    }


    /**
     * Get all ledger entries for a member
     */
    public function ledgers()
    {
        return $this->hasMany(Ledger::class);
    }


    /**
     * Get all monthly commitments for a member
     */
    public function monthly_savings()
    {
        return $this->hasMany(MonthlySaving::class, 'ippis', 'ippis');
    }


    /**
     * Get last monthly commitment for a member
     */
    public function latest_monthly_saving()
    {
        return $this->monthly_savings->last();
    }


    /**
     * Get all monthly payments for a member
     */
    public function monthly_savings_payments()
    {
        return $this->hasMany(MonthlySavingsPayment::class, 'ippis', 'ippis');
    }

    /**
    * Get the last payment made for a monthly savings
    */
    public function latest_monthly_savings_payment()
    {
        return $this->monthly_savings_payments->where('is_authorized', 1)->last();
    }


    /**
     * Get all long term loans for a member
     */
    public function long_term_loans()
    {
        return $this->hasMany(LongTerm::class, 'ippis', 'ippis');
    }

    /**
     * Get latest long term loan of a member
     */
    public function latest_long_term_loan()
    {
        return $this->long_term_loans->last();
    }

    /**
     * Get all payments tied to a loan
     */
    public function long_term_payments()
    {
        return $this->hasMany(LongTermPayment::class, 'ippis', 'ippis');
    }

    /**
    * Get the last payment made for a long term loan
    */
    public function latest_long_term_payment()
    {
        return $this->long_term_payments->where('is_authorized', 1)->last();
    }

    /**
     * Get all long term loan defaults for a member
     */
    public function long_term_loans_defaults()
    {
        return $this->hasMany(LongTermLoanDefault::class, 'ippis', 'ippis');
    }

    /**
     * Get all short term loans for a member
     */
    public function short_term_loans()
    {
        return $this->hasMany(ShortTerm::class, 'ippis', 'ippis');
    }

    /**
     * Get latest short term loans of a member
     */
    public function latest_short_term_loan()
    {
        return $this->short_term_loans->last();
    }

    /**
     * Get all payments tied to a short term loan
     */
    public function short_term_payments()
    {
        return $this->hasMany(ShortTermPayment::class, 'ippis', 'ippis');
    }

    /**
    * Get the last payment made for a short term loan
    */
    public function latest_short_term_payment()
    {
        return $this->short_term_payments->where('is_authorized', 1)->last();
    }

    /**
     * Get all short term loan defaults for a member
     */
    public function short_term_loans_defaults()
    {
        return $this->hasMany(ShortTermLoanDefault::class, 'ippis', 'ippis');
    }

    /**
     * Get all commodity loans for a member
     */
    public function commodities_loans()
    {
        return $this->hasMany(Commodity::class, 'ippis', 'ippis');
    }

    /**
     * Get latest commodity loan of a member
     */
    public function latest_commodity_loan()
    {
        return $this->commodities_loans->last();
    }

    /**
     * Get all payments tied to a commodity loan
     */
    public function commodities_loans_payments()
    {
        return $this->hasMany(CommodityPayment::class, 'ippis', 'ippis');
    }

    /**
    * Get the last payment made for a commodity loan
    */
    public function latest_commodities_payment()
    {
        return $this->commodities_loans_payments->where('is_authorized', 1)->last();
    }

    /**
     * Get all commodity loan defaults for a member
     */
    public function commodities_loans_defaults()
    {
        return $this->hasMany(CommodityLoanDefault::class, 'ippis', 'ippis');
    }

    /**
     * Get a members paypoint
     */
    public function member_pay_point()
    {
        return $this->belongsTo(Center::class, 'pay_point', 'id')->orderBy('name');
    }

    /**
     * Get a members savings balance
     */
    public function savingsBalance()
    {
        $savings = MonthlySavingsPayment::where('ippis', $this->ippis)->where('is_authorized', 1)->latest('id')->first();
        return $savings ? $savings->bal : 0;
    }

    /**
     * Get a members long term loan balance
     */
    public function longTermLoanBalance()
    {
        $ltp = LongTermPayment::where('ippis', $this->ippis)->where('is_authorized', 1)->latest('id')->first();

        return $ltp ? $ltp->bal : 0;
    }

    /**
     * Get a members long term loan balance
     */
    public function shortTermLoanBalance()
    {
        $stp = ShortTermPayment::where('ippis', $this->ippis)->where('is_authorized', 1)->latest('id')->first();
        return $stp ? $stp->bal : 0;
    }

    /**
     * Get a members commodity loan balance
     */
    public function commodityLoanBalance()
    {
        $commP = CommodityPayment::where('ippis', $this->ippis)->where('is_authorized', 1)->latest('id')->first();
        return $commP ? $commP->bal : 0;
    }

    /**
     * Get a members long term loan balance
     */
    public function sharesBalance()
    {
        $share = Share::where('ippis', $this->ippis)->latest('id')->first();

        return $share ? $share->amount : 0;
    }

    public function toggleStatus() {
        $this->is_active = !$this->is_active;

        if ($this->is_active) {
            $this->deactivation_date = Carbon::now();
        } else {
            $this->activation_date = Carbon::now();
        }

        return $this;
    }

    /**
     * Ensure member details have been entered
     * Details to ensure are Phone number, email, paypoint, centre
     */
    public function ensureMemberDetails() {

        if($this->phone && $this->email && $this->center_id && $this->pay_point):
            return TRUE;
        else:
            return FALSE;
        endif;
    }

    public static function getMemberBanks($ippis) {

        $member = Member::where('ippis', $ippis)->first();

        $banks = [];
        if($member->primary_bank) {
            $banks[] = $member->primary_bank_details;
        }
        if($member->secondary_bank) {
            $banks[] = $member->secondary_bank_details;
        }

        return $banks;
    }
}

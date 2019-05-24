<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewPrivateCustomers extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_private_customers', function (Blueprint $table) {
            $table->increments('id');

            $table->string('recurring_invoice_id')->nullable();
            $table->date('next_recurring_date')->nullable();
            $table->integer('customer_id')->nullable();
            $table->string('site_name')->nullable();
            $table->string('customer_title')->nullable();
            $table->string('company_name')->nullable();
            $table->string('customer_given_name')->nullable();
            $table->string('customer_family_name')->nullable();
            $table->string('customer_address')->nullable();
            $table->string('customer_city')->nullable();
            $table->string('customer_state')->nullable();
            $table->string('customer_postal_code')->nullable();
            $table->string('site_address')->nullable();
            $table->string('site_city')->nullable();
            $table->string('site_state')->nullable();
            $table->string('site_postal_code')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_company')->nullable();
            $table->string('period')->nullable();
            $table->string('payer_reference')->nullable();
            $table->string('direct_date')->nullable();
            $table->string('payer_account_name')->nullable();
            $table->boolean('is_processed')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('docx')->nullable();
            $table->string('pdf')->nullable();


            $table->index(['customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('new_private_customers');
    }
}

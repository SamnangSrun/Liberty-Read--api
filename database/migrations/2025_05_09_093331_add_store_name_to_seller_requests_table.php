<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_store_name_to_seller_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStoreNameToSellerRequestsTable extends Migration
{
    public function up()
    {
        Schema::table('seller_requests', function (Blueprint $table) {
            $table->string('store_name')->after('name');
        });
    }

    public function down()
    {
        Schema::table('seller_requests', function (Blueprint $table) {
            $table->dropColumn('store_name');
        });
    }
}

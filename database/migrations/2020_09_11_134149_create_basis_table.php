<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBasisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('basis', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id')->comment('主键id');
            $table->string('username')->comment('学号');
            $table->string('name')->comment('姓名');
            $table->string('classname')->comment('班级');
            $table->string('project')->comment('考核项目名称');
            $table->integer('grade')->comment('考核成绩');
            $table->integer('hour')->nullable()->comment('考核小时数');
            $table->integer('minute')->nullable()->comment('考核分钟数');
            $table->integer('second')->nullable()->comment('考核秒数');

            $table->timestamp('addtime')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('添加时间');
            $table->timestamp('updatetime')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('修改时间');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('basis');
    }
}

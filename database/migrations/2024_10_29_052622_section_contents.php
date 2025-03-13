<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('section_contents', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('language_id');
            $table->string('category_section_title')->nullable(); 
            $table->string('latest_service_section_title')->nullable(); 
            $table->string('featured_service_section_title')->nullable(); 
            $table->string('vendor_section_title')->nullable(); 
            $table->string('hero_section_background_img')->nullable(); 
            $table->string('hero_section_title')->nullable();
            $table->string('hero_section_subtitle')->nullable(); 
            $table->string('workprocess_section_title')->nullable(); 
            $table->string('workprocess_section_subtitle')->nullable(); 
            $table->string('workprocess_section_btn')->nullable();
            $table->string('workprocess_section_url')->nullable();
            $table->string('workprocess_icon')->nullable(); 
            $table->string('work_process_background_img')->nullable();
            $table->string('call_to_action_section_image')->nullable();
            $table->string('call_to_action_section_inner_image')->nullable(); 
            $table->string('call_to_action_section_title')->nullable(); 
            $table->string('call_to_action_section_btn')->nullable(); 
            $table->string('call_to_action_icon')->nullable(); 
            $table->string('call_to_action_url')->nullable(); 
            $table->text('action_section_text')->nullable(); 
            $table->string('testimonial_section_image')->nullable(); 
            $table->string('testimonial_section_title')->nullable(); 
            $table->string('testimonial_section_subtitle')->nullable(); 
            $table->string('testimonial_section_clients')->nullable(); 
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
        Schema::dropIfExists('section_contents');
    }
};

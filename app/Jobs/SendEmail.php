<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Collection;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   

        $data=DB::table('teklif')->orderBy('id', 'DESC')->first();


        $website =DB::table('websites')
        ->where('websites.id', '=',$data->website_id)
        ->first();
    
            $message = array(
                'fullname' =>$data->fullname,
                'email'=>$data->email,
                'telefon'=>$data->telefon,
                'product'  =>$data->product,
                'note'=>$data->note,
                'tarih'=>$data->created_at,
                'website'=>$website->title
             );

             try {
                Mail::send(['text'=>'mail'],$message,function ($m)  use ($message) {
                    $a = " Teklif Talebi: ";
                    $a .= $message['fullname'];
                    $a .= " | ";
                    $a .=  $message['website'];
                    $m->from($message['email'],'Teklif Talebi');
                    $m->to('halilbuyukkilic007@gmail.com')->subject($a);
                });
                
            }catch (Exception $e) {
            //throw $th;
            $monolog = Log::getMonolog();
           }

       
        
   
    }
}

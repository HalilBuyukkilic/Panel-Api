<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use App\Models\Quote;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendEmail;
use Carbon\Carbon;

class TeklifController extends Controller
{

    public function index($website_slug)
    {

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        $data = DB::table('teklif')

            //->leftjoin('dil','dil.id','=','referanslar.dil_ID')

            ->select(
                'teklif.id',
                'teklif.fullname',
                'teklif.email',
                'teklif.telefon',
                'teklif.product',
                'teklif.note',
                'teklif.slug',
                'teklif.created_at',
                'teklif.updated_at'
            )
            ->where('teklif.website_id', '=', $website->id)
            ->orderBy('id', 'DESC')
            ->get();

        return  response()->json($data);
    }

    public function show($website_slug, $id)
    {


        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        $teklif = Quote::where('id', $id)->get();;

        if ($teklif != null) {

            return response()->json($teklif);
        } else {
            $result = array('Success' => false, 'Message' => 'Böyle bir teklif bulunmamaktadır,silinmiş veya hiç eklenmemiş olabilir');
            return response()->json($result);
        }
    }

    public function store(Request $request, $website_slug)
    {

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        try {
            $request->merge([

                'slug' => Str::slug($request->email)
            ]);

            $teklif = Quote::create([
                'website_id' => $website->id,
                'fullname' => $request->input('fullname'),
                'email' => $request->input('email'),
                'telefon' => $request->input('telefon'),
                'product'  => $request->input('product'),
                'note' => $request->input('note'),
                'slug' => $request->input('slug'),

            ]);


            $message = array(
                'fullname' => $request->input('fullname'),
                'email' => $request->input('email'),
                'telefon' => $request->input('telefon'),
                'product'  => $request->input('product'),
                'note' => $request->input('note'),
                'tarih' => $teklif->created_at,
                'website' => $website_slug,
                'websiteForTitle' => $website->title
            );


            try {
                //code...
                // Mail::send(['text'=>'mail'],$message,function ($m)  use ($message) {
                //     $a = " Teklif Talebi: ";
                //     $a .= $message['fullname'];
                //     $a .= " | ";
                //     $a .=  $message['websiteForTitle'];
                //     $m->from($message['email'],'Teklif Talebi');
                //     $m->to('teklif@perasis.com.tr')->subject( $a );
                // });

                $job = (new SendEmail())->delay(Carbon::now()->addSeconds(10));

                dispatch($job);
            } catch (Exception $th) {
                //throw $th;
            }



            $result = array('Success' => true, 'Message' => 'teklif eklendi');

            return response()->json($result);
        } catch (QueryException $e) {
            $error_code = $e->errorInfo[1];
            if ($error_code == 1062) {
                $result = array('DublicateError' => 'duplicate');
                return $result;
            }
        }
    }

    public function delete(Request $request, $website_slug, $id)
    {

        $teklif = Quote::where('id', $id)->first();
        $teklif_id = $teklif->id;

        if ($teklif == null) {
            $result = array('Success' => true, 'Message' => 'Bu teklif silinmiş veya hiç eklenmemiş olabilir');
            return response()->json($result);
        } else {

            $teklif->delete();

            $result = array('Success' => true, 'Message' => 'Teklif silindi');

            return response()->json($result);
        }
    }


    public function indexteklifAll()
    {
        $data = DB::table('teklif')
            ->leftjoin('websites', 'websites.id', '=', 'teklif.website_id')
            ->select(
                'teklif.id',
                'teklif.fullname',
                'teklif.email',
                'teklif.telefon',
                'teklif.product',
                'teklif.note',
                'teklif.website_id',
                'websites.title as website',
                'teklif.slug',
                'teklif.created_at',
                'teklif.updated_at'
            )
            ->orderBy('id', 'DESC')
            ->get();

        return  response()->json($data);
    }

    public function showteklifPanel($id)
    {


        //$teklif=Quote::where('id',$id)->get();
        $teklif = Quote::with(['websites'])
            ->where('id', '=', $id)
            ->get();
        if ($teklif != null) {

            return response()->json($teklif);
        } else {
            $result = array('Success' => false, 'Message' => 'Böyle bir teklif bulunmamaktadır,silinmiş veya hiç eklenmemiş olabilir');
            return response()->json($result);
        }
    }

    public function deleteteklifPanel(Request $request, $id)
    {

        $teklif = Quote::where('id', $id)->first();
        $teklif_id = $teklif->id;

        if ($teklif == null) {
            $result = array('Success' => true, 'Message' => 'Bu teklif silinmiş veya hiç eklenmemiş olabilir');
            return response()->json($result);
        } else {

            $teklif->delete();

            $result = array('Success' => true, 'Message' => 'Teklif silindi');

            return response()->json($result);
        }
    }
}

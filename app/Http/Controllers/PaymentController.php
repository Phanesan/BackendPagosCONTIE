<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utils\JsonFormatter;
use Exception;
use Illuminate\Validation\ValidationException;
use App\Models\Payment;
use App\Models\Article;
use App\Models\User;
use App\Models\Setting;

class PaymentController extends Controller
{
    public function show()
    {
        return view("payments.index");
    }

    public function showBuy(){
        return view("payments.buy");
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $payments = Payment::all();
            return JsonFormatter::successFormatJson("Payments retrieved successfully", 0, $payments);
        } catch(Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function open(Request $request)
    {
        try {
            $this->validate($request, Payment::$rules);

            $payment = new Payment();
            $payment->user_id = $request->user_id;
            $payment->amount = $request->amount;
            $payment->article_id = $request->article_id;
            $payment->save();

            return JsonFormatter::successFormatJson("Payment created successfully", 0, $payment);
        } catch(ValidationException $e) {
            return JsonFormatter::errorFormatJson('Validation error', -2, $e->getMessage());
        } catch(Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    public function cancel(string $id) {
        try {
            $payment = Payment::find($id);
            if(!$payment) {
                return JsonFormatter::errorFormatJson('Payment not found', -3, null);
            }
            $payment->payment_status = 'CANCELLED';
            $payment->update();
            return JsonFormatter::successFormatJson("Payment cancelled successfully", 0, $payment);
        } catch(Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    public function close(string $id) {
        try {
            $payment = Payment::find($id);
            if(!$payment) {
                return JsonFormatter::errorFormatJson('Payment not found', -3, null);
            }
            $payment->payment_status = 'CLOSED';
            $payment->update();
            return JsonFormatter::successFormatJson("Payment closed successfully", 0, $payment);
        } catch(Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function get(string $id)
    {
        try {
            $payment = Payment::find($id);
            if(!$payment) {
                return JsonFormatter::errorFormatJson('Payment not found', -3, null);
            }
            return JsonFormatter::successFormatJson("Payment retrieved successfully", 0, $payment);
        } catch(Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $payment = Payment::find($id);
            if(!$payment) {
                return JsonFormatter::errorFormatJson('Payment not found', -3, null);
            }
            $this->validate($request, Payment::$updateRules);

            $payment->amount = $request->amount;

            $article = Article::find($request->article_id);
            if(!$article) {
                return JsonFormatter::errorFormatJson('Article not found', -3, null);
            }

            $payment->article_id = $request->article_id;
            $payment->update();

            return JsonFormatter::successFormatJson("Payment updated successfully", 0, $payment);
        } catch(ValidationException $e) {
            return JsonFormatter::errorFormatJson('Validation error', -2, $e->getMessage());
        } catch(Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $payment = Payment::find($id);
            if(!$payment) {
                return JsonFormatter::errorFormatJson('Payment not found', -3, null);
            }
            $payment->delete();
            return JsonFormatter::successFormatJson("Payment deleted successfully", 0, null);
        } catch(Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    public function status(Request $request, string $id) {
        try {
            $payment = Payment::find($id);
            if(!$payment) {
                return JsonFormatter::errorFormatJson('Payment not found', -3, null);
            }
            $payment->payment_status = $request->status;
            $payment->update();
            return JsonFormatter::successFormatJson("Payment status updated successfully", 0, $payment);
        } catch(Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    public function getDataForPayment($id) {
        try {
            $article = Article::find($id);

            if(!$article) {
                return JsonFormatter::errorFormatJson('Article not found', -3, null);
            }

            $authors = $article->authors();

            $dataAuthors = $authors->get();
            foreach($dataAuthors as $author) {
                $rows[] = [
                    'name' => $author->name,
                    'email' => $author->email,
                ];
            }

            $author = $authors->first();
            $countPaper = User::find($author->id)->articles()->count();
            $rates = Setting::where('key', '=', 'rates')->first()->value;

            return JsonFormatter::successFormatJson("Article data retrieved successfully", 0, [
                'totalPrice' => $countPaper * $rates,
                'individualPrice' => $rates,
                'rows' => $rows
            ]);
            
        } catch(Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }
}

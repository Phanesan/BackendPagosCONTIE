<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use App\Models\User;
use App\Models\Payment;
use App\Models\Article;

class PaymentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $articles = Article::all();
        
        foreach ($users as $user) {
            foreach ($articles as $article) {
                $payment = new Payment();
                $payment->user_id = $user->id;
                $payment->amount = rand(300, 2000);
                $payment->article_id = $article->id;
                $payment->save();
            }
        }
            
    }
}

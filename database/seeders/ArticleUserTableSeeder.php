<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArticleUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $articles = Article::all();
        $users->each(function ($user) use ($articles) {
            $articles->each(function ($article) use ($user) {
                $user->articles()->attach($article->id, [
                    'position' => rand(1, 5),
                ]);
            });
        });
    }
}

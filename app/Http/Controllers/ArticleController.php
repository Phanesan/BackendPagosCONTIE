<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use Illuminate\Http\Request;
use App\Utils\JsonFormatter;
use Exception;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class ArticleController extends Controller
{
    public function showDetails()
    {
        return view("articles.details");
    }

    public function index()
    {
        $articles = Article::paginate();
        return view('articles.index', get_defined_vars());
    }

    public function create()
    {
        return view('articles.add', get_defined_vars());
    }


    public function store(ArticleRequest $request)
    {
        Article::create($request->validated());
        return redirect()->route('articles')->with('success', 'Artículo registrado correctamente');
    }

    public function edit($id)
    {
        $article = Article::find($id);

        return view('articles.edit', get_defined_vars());
    }

    public function update(ArticleRequest $request)
    {
        $article = Article::find($request->id);

        if (!$article) {
            return redirect()->back()->with('error', 'Artículo no encontrado');
        }

        $article->update($request->validated());

        return redirect()->route('articles')->with('success', 'Artículo actualizado correctamente');
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return redirect()->back()->with('success', 'Artículo eliminado correctamente');
    }

    public function getAll() {
        try {
            $articles = Article::all();
            return JsonFormatter::successFormatJson("Articles retrieved successfully", 0, $articles);
        } catch(Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    public function save(Request $request) {
        try {
            $this->validate($request, Article::$rules);

            $article = new Article();

            $article->title = $request->title;
            $article->content = $request->content;

            $article->save();

            return JsonFormatter::successFormatJson("Article created successfully", 0, $article);
        } catch(ValidationException $e) {
            return JsonFormatter::errorFormatJson('Validation error', -2, $e->getMessage());
        } catch(Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    public function search(string $id) {
        try {
            $article = Article::find($id);
            if(!$article) {
                return JsonFormatter::errorFormatJson('Article not found', -3, null);
            }
            return JsonFormatter::successFormatJson("Article retrieved successfully", 0, $article);
        } catch(Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    public function refresh(Request $request, string $id) {
        try {
            $article = Article::find($id);
            if(!$article) {
                return JsonFormatter::errorFormatJson('Article not found', -3, null);
            }
            
            $this->validate($request, Article::$rules);
            $article->title = $request->title;
            $article->content = $request->content;

            $article->update();
            return JsonFormatter::successFormatJson("Article updated successfully", 0, $article);
        } catch(ValidationException $e) {
            return JsonFormatter::errorFormatJson('Validation error', -2, $e->getMessage());
        } catch(Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    public function delete(string $id) {
        try {
            $article = Article::find($id);
            if(!$article) {
                return JsonFormatter::errorFormatJson('Article not found', -3, null);
            }
            $article->delete();
            return JsonFormatter::successFormatJson("Article deleted successfully", 0, null);
        } catch(Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }
    
    public function downloadDocument() {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $sheet->setCellValue('A1',"#");
            $sheet->setCellValue("B1","Authors");
            $sheet->setCellValue("C1","Title");
            $sheet->setCellValue("D1","Paper");
            $sheet->setCellValue("E1","Time");
            $sheet->setCellValue("F1","Email");

            $articles = Article::all();

            foreach($articles as $key => $article) {
                // Obtener los autores y correos del artículo
                $authorsData = $article->authors()->get();
                $authors = "";
                $mails = "";
                foreach($authorsData as $author) {
                    $authors .= $author->name .", ";

                    if(strpos($author->mail, "UndefinedMail") !== false) {
                        continue;
                    }

                    $mails .= $author->email .", ";
                }

                $sheet->setCellValue("A".($key + 2), $article->id);
                $sheet->setCellValue("B".($key + 2), $authors);
                $sheet->setCellValue("C".($key + 2), $article->title);
                $sheet->setCellValue("D".($key + 2), "✔");
                $sheet->setCellValue("E".($key + 2), $article->publication_date);
                $sheet->setCellValue("F".($key + 2), $mails);
            }

            $writter = new Csv($spreadsheet);
            $writter->setUseBOM(true);
            $document = "CONTIE-ARTICULOS.csv";
            $writter->save($document);
            return response()->download($document, "CONTIE-ARTICULOS.csv");
        } catch(Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

}

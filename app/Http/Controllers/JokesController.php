<?php

namespace App\Http\Controllers;

use Response;
use Validator;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Input;

use App\Http\Requests;

use App\Joke;
use App\User;
use App\Transformers\JokeTransformer;


class JokesController extends Controller
{
    protected $jokeTransformer;

    public function __construct()
    {
//        parent::__construct($this->skip_authentication=true);
        // $this->middleware('auth.basic');
//        $this->middleware('jwt.auth');
        //$this->middleware('auth');
//        $this->middleware('auth.token');

        $this->jokeTransformer = new JokeTransformer();
    }

    public function index(){
//        echo trans('msg.insert.success', ['modul' => trans('lib.modul.master.produk')]);
//        echo trans('message.test.test2');
        $next_id = \DB::select("select nextval('hibernate_sequence')");
        dd($next_id);
    }

    public function indexd(Request $request)
    {
        $search_term = $request->input('search');
        $limit = $request->input('limit') ? $request->input('limit') : 20;

        if ($search_term) {
            $jokes = Joke::orderBy('id', 'DESC')->where('body', 'ilike', "%$search_term%")
                ->with(
                array('User' => function ($query) {
                    $query->select('id', 'name');
                })
            )->select('id', 'body', 'user_id')->paginate($limit);

            $jokes->appends(array(
                'search' => $search_term,
                'limit' => $limit
            ));
        } else {
            $jokes = Joke::orderBy('id', 'DESC')->with(
                array('User' => function ($query) {
                    $query->select('id', 'name');
                })
            )->select('id', 'body', 'user_id')->paginate($limit);

            $jokes->appends(array(
                'limit' => $limit
            ));
        }

        return Response::json($this->jokeTransformer->transformCollection($jokes), 200)->header('X-MESSAGE', 'test message');
    }


    public function show($id)
    {
        $joke = Joke::with(
            array('User' => function ($query) {
                $query->select('id', 'name');
            })
        )->find($id);

        if (!$joke) {
            return Response::json([
                'error' => [
                    'message' => 'Joke does not exist'
                ]
            ], 404);
        }

        $previous = Joke::where('id', '<', $joke->id)->max('id');

        $next = Joke::where('id', '>', $joke->id)->min('id');

        return Response::json([
            'previous_joke_id' => $previous,
            'next_joke_id' => $next,
            'data' => $this->jokeTransformer->transform($joke)
        ], 200);
    }

    public function store(Request $request)
    {
        $messages = array(
            'body.required' => 'Please provide body',
            'user_id.required' => 'Please provide user',
        );
        $rules = array(
            'body'  => 'required',
            'user_id'  => 'required',
        );

        $validator  = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->errors()
            ], 422);
        }
        $joke = Joke::create($request->all());

        return Response::json([
            'message' => 'Joke Created Succesfully',
            'data' => $this->jokeTransformer->transform($joke)
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!$request->body or !$request->user_id) {
            return Response::json([
                'error' => [
                    'message' => "Please provide both body and user_id"
                ]
            ], 422);
        }
        $joke = Joke::find($id);
        $joke->body = $request->body;
        $joke->user_id = $request->user_id;
        $joke->save();

        return Response::json([
            'message' => 'Joke Updated Succesfully'
        ]);
    }


    public function destroy($id)
    {
        Joke::destroy($id);
    }

}

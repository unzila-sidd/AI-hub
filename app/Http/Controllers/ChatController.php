<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{


    protected $openAI;



    public function __construct(OpenAIService $openAI)
    {
        $this->openAI=$openAI;
    }





    public function index()
    {

        $conversations =
        Conversation::where(
            'user_id',
            Auth::id()
        )
        ->latest()
        ->get();


        return view(
            'chat.index',
            compact('conversations')
        );

    }





    public function send(Request $request)
    {


        $request->validate([

            'message'=>'required|string',

            'conversation_id'=>'nullable'

        ]);




        /*
        Create new conversation
        */

        if(!$request->conversation_id)
        {


            $conversation =
            Conversation::create([

                'user_id'=>Auth::id(),

                'title'=>substr(
                    $request->message,
                    0,
                    40
                )

            ]);

        }
        else
        {


            $conversation =
            Conversation::where(
                'user_id',
                Auth::id()
            )
            ->findOrFail(
                $request->conversation_id
            );


        }





        /*
        Save User Message
        */


        Message::create([

            'conversation_id'=>$conversation->id,

            'role'=>'user',

            'message'=>$request->message

        ]);







        /*
        Prepare AI History
        */


        $history =
        $conversation
        ->messages()
        ->latest()
        ->take(10)
        ->get()
        ->reverse()
        ->map(function($msg){

            return [

                'role'=>$msg->role,

                'content'=>$msg->message

            ];

        })
        ->toArray();







        /*
        Send to OpenAI
        */


        $reply =
        $this->openAI->chat(
            $history
        );







        /*
        Save AI Response
        */


        Message::create([

            'conversation_id'=>$conversation->id,

            'role'=>'assistant',

            'message'=>$reply

        ]);






        return response()->json([


            'conversation_id'=>$conversation->id,


            'reply'=>$reply


        ]);


    }






    public function load($id)
    {

        $conversation =
        Conversation::with('messages')
        ->where('user_id',Auth::id())
        ->findOrFail($id);



        return response()->json(
            $conversation
        );

    }


}

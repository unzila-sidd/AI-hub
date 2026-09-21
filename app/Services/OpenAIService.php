<?php

namespace App\Services;

use OpenAI;

class OpenAIService
{

    protected $client;


    public function __construct()
    {
        $this->client = OpenAI::client(env('OPENAI_API_KEY'));
    }



    public function chat($messages)
    {

        $response = $this->client
            ->chat()
            ->create([

                'model' => 'gpt-4o-mini',

                'messages' => $messages,

                'temperature' => 0.7

            ]);


        return $response
            ->choices[0]
            ->message
            ->content;

    }


}

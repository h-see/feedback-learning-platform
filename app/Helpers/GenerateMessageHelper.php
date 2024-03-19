<?php

namespace App\Helpers;

use App\Persona\Models\Persona;
use App\Counselling\Models\Counselling;
use Orhanerday\OpenAi\OpenAi;
use Symfony\Component\Yaml\Yaml;


class GenerateMessageHelper
{
    /**
     * Generates a new vikl-message with OpenAi ChatGPT
     */
    public static function generateWithOpenAi($personaId, $counsellingId)
    {
        $persona = Persona::find($personaId);
        // load chat history
        $chatHistory = GenerateMessageHelper::getChatHistory($counsellingId);
        if ($chatHistory === '') {
            $chatHistory = 'Noch keine Nachrichten vorhanden. Schreibe die erste Nachricht.';
        } else {
            $chatHistory = str_replace('{vikl}', $persona->name, $chatHistory);
            $chatHistory = str_replace('{user}', 'Berater', $chatHistory);
        }

        // build input for ChatGPT
        $setupRoleplay = GenerateMessageHelper::readYamlFile(base_path('setup-roleplay-template-openai.yaml'));
        $infos = "";
        foreach ($persona->properties['Steckbrief'] as $key => $value) {
            $infos .= $key . ': ' . $value . ', ';
        }
        $personality = $infos . $persona->properties['Hauptanliegen'];
        $setupRoleplay = str_replace('{name}', $persona->name, $setupRoleplay);
        $setupRoleplay = str_replace('{personality_condition}', $personality, $setupRoleplay);
        $setupRoleplay = str_replace('{chat_history}', $chatHistory, $setupRoleplay);

        // generate new Message
    $open_ai = new OpenAi(env('OPENAI_KEY'));
        $chat = $open_ai->chat([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    "role" => "system",
                    "content" => $setupRoleplay
                ]
            ],
            'temperature' => 1.0,
            'max_tokens' => 3000,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ]);
        $d = json_decode($chat);
        $gptResponse = $d->choices[0]->message->content;
        $substringToRemove = $persona->name . ': ';
        // sometimes theres "personaName: " in front of the text
        if (strpos($gptResponse, $substringToRemove) === 0) {
            $gptResponse = substr($gptResponse, strlen($substringToRemove));
        }
        return $gptResponse;
    }

    protected static function readYamlFile($filePath) {
        if (file_exists($filePath)) {
            $yamlContents = file_get_contents($filePath);
            return $yamlContents;
        } else {
            return false;
        }
    }

    protected static function getChatHistory($counsellingId) {
        $counselling = Counselling::find($counsellingId)->load('counsellingMessages');

        $messageHistory = '';
        foreach ($counselling->counsellingMessages as $message) {
            $author = $message->author;
            $content = $message->content;
            $messageHistory .= "[{" . $author . "}: $content],";
        }
        return $messageHistory;
    }
}

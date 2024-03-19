<?php

namespace App\Helpers;

use App\Counselling\Models\CounsellingFeedback;
use App\Persona\Models\Persona;
use Orhanerday\OpenAi\OpenAi;

class GenerateFeedbackHelper extends GenerateMessageHelper
{
    public static function generateFeedbackWithOpenAi($personaId, $counsellingId, $feedbackTypes){
        $persona = Persona::find($personaId);
        // load chat history
        $chatHistory = GenerateMessageHelper::getChatHistory($counsellingId);
        $chatHistory = str_replace('{vikl}', $persona->name, $chatHistory);
        $chatHistory = str_replace('{user}', 'Berater', $chatHistory);

        $generatedFeedbacks = [];

        foreach($feedbackTypes as $feedbackType){
            if($feedbackType !== 5){
                // build input for ChatGPT
                $setupFeedback = GenerateMessageHelper::readYamlFile(base_path('setup-feedback-prompt-openai.yaml'));
                $setupFeedback = str_replace('{feedback_type}', $feedbackType->name, $setupFeedback);
            }
            // client feedback
            else{
                // build input for ChatGPT
                $setupFeedback = GenerateMessageHelper::readYamlFile(base_path('setup-client-feedback-prompt-openai.yaml'));
            }

            $setupFeedback = str_replace('{feedback_type_desc}', $feedbackType->description, $setupFeedback);
            $setupFeedback = str_replace('{klient}', $persona->name, $setupFeedback);
            $setupFeedback = str_replace('{chat_history}', $chatHistory, $setupFeedback);

            // generate Feedback
            $open_ai = new OpenAi(env('OPENAI_KEY'));
            $chat = $open_ai->chat([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        "role" => "system",
                        "content" => $setupFeedback
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 1500,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
            ]);
            $d = json_decode($chat);
            $gptResponse = $d->choices[0]->message->content;

            $ai_feedback_properties = array(
                'prompt' => $setupFeedback,
                'api_response' => json_encode($chat, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );

            if($feedbackType === 5){
                if(!json_validate($gptResponse)){
                    $start = strpos($gptResponse, '{');
                    $end = strrpos($gptResponse, '}');

                    if ($start !== false && $end !== false) {
                        $validGptResponse = substr($gptResponse, $start, $end - $start + 1);
                        $gptResponse = $validGptResponse;
                    }
                }
            }

            $counsellingFeedback = CounsellingFeedback::create([
                'counselling_id' => $counsellingId,
                'status_feedback_id' => 4,
                'feedback_source_id' => 3,
                'feedback_text' => $gptResponse,
                'feedback_types_id' => $feedbackType->id,
                'ai_feedback_properties' => json_encode($ai_feedback_properties, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            $generatedFeedbacks[] = $counsellingFeedback;
        }

        return $generatedFeedbacks;
    }
}

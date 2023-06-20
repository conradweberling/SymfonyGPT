<?php

namespace App\Controller;

use App\Exception\RequestNotValid;
use App\Exception\ResponseNotValid;
use Exception;
use Orhanerday\OpenAi\OpenAi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    #[Route('/', name: 'chat_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('chat/index.html.twig', [
            'chat_name' => $this->getParameter('chat_name'),
        ]);
    }

    #[Route('/chat/api/parameters', name: 'chat_api_parameters', methods: ['GET'])]
    public function parametersApi(): JsonResponse
    {
        return new JsonResponse([
            'chatName' => $this->getParameter('chat_name'),
            'chatWelcomePrompt' => $this->getParameter('chat_welcome_prompt'),
        ]);
    }

    /**
     * @throws ResponseNotValid
     * @throws RequestNotValid
     * @throws Exception
     */
    #[Route('/chat/api/message', name: 'chat_api_message', methods: ['POST'])]
    public function messageApi(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent());

        if (!is_object($payload) || !property_exists($payload, 'conversation')) {
            throw new RequestNotValid('Payload is not valid.');
        }

        $conversation = $payload->conversation;

        if (!is_array($conversation)) {
            throw new RequestNotValid('Conversation is not valid!');
        }

        // Add CHAT_USER_SUFFIX_PROMPT
        foreach ($conversation as $key => $item) {
            if ($item->role === 'user') {
                $conversation[$key]->content = implode(' - ', [
                    $item->content,
                    $this->getParameter('chat_user_suffix_prompt')
                ]);
            }
        }

        // Add CHAT_SYSTEM_PROMPT
        $systemMessages = [
            [
                "role" => "system",
                "content" => $this->getParameter('chat_system_prompt')
            ]
        ];

        $messages = array_merge($systemMessages, $conversation);

        $client = new OpenAi(
            $this->getParameter('openai_apikey')
        );

        $chatResponse = $client->chat([
            'model' => $this->getParameter('openai_model'),
            'messages' => $messages,
            'temperature' => 1.0,
            'max_tokens' => 4000,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ]);

        $chat = json_decode($chatResponse);

        try {
            $responseContent = $chat->choices[0]->message->content;
        } catch (Exception) {
            throw new ResponseNotValid('OpenAI API Response is not valid: '.$chatResponse);
        }

        return new JsonResponse([
            'content' => $responseContent
        ]);
    }
}

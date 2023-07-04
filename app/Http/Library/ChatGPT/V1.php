<?php

namespace App\Http\Library\ChatGPT;

use Exception;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Ramsey\Uuid\Uuid;

class V1
{
    private $baseUrl = 'https://ai.fakeopen.com/api/';

    private $accounts = [];

    private $http;

    public function __construct(string $baseUrl = null, int $timeout = 360)
    {
        if ($baseUrl) {
            $this->baseUrl = $baseUrl;
        }

        $this->http = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $timeout,
            'stream' => true,
        ]);
    }

    /**
     * addAccount
     *
     * @param  string  $accessToken
     * @param  mixed  $name
     * @param  bool  $paid
     * @param  string|null  $model
     *
     * @return void
     */
    public function addAccount(string $accessToken, $name = null, bool $paid = false, string $model = null): void
    {
        if ($name === null) {
            $this->accounts[] = [
                'access_token' => $accessToken,
                'paid' => $paid,
                'model' => $model,
            ];
        } else {
            $this->accounts[$name] = [
                'access_token' => $accessToken,
                'paid' => $paid,
                'model' => $model,
            ];
        }
    }

    /**
     * getAccount
     *
     * @param  string  $name
     *
     * @return array
     */
    public function getAccount(string $name): array
    {
        return $this->accounts[$name];
    }

    /**
     * getAccounts
     * @return array
     */
    public function getAccounts(): array
    {
        return $this->accounts;
    }

    /**
     * ask
     *
     * @param  string  $prompt
     * @param  string|null  $conversationId
     * @param  string|null  $parentId
     * @param  mixed  $account
     * @param  bool  $stream
     *
     * @return Generator
     * @throws Exception|GuzzleException
     */
    public function ask(
        string $prompt,
        string $conversationId = null,
        string $parentId = null,
        $account = null,
        bool $stream = false
    ): Generator {
        if ($account === null) {
            $account = array_rand($this->accounts);

            try {
                $token = $this->accessTokenToJWT($this->accounts[$account]['access_token']);
            } catch (Exception $e) {
                throw new Exception("Account ".$account." is invalid");
            }
        } else {
            $token = isset($this->accounts[$account]['access_token']) ? $this->accessTokenToJWT($this->accounts[$account]['access_token']) : null;
        }

        if ($token === null) {
            throw new Exception("No account available");
        }

        if ($parentId !== null && $conversationId === null) {
            throw new Exception("conversation_id must be set once parent_id is set");
        }

        if ($conversationId === null && $parentId === null) {
            $parentId = (string) Uuid::uuid4();
        }

        if ($conversationId !== null && $parentId === null) {
            try {
                $response = $this->http->get('conversation/'.$conversationId, [
                    'headers' => [
                        'Authorization' => $token,
                        'Content-Type' => 'application/json',
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36 Edg/110.0.1587.63',
                        'Referer' => 'https://chat.openai.com/chat',
                    ],
                ]);
            } catch (GuzzleException $e) {
                throw new Exception("Request failed: ".$e->getMessage());
            }

            $response = json_decode($response->getBody()->getContents(), true);
            if (isset($response['current_node'])) {
                $conversationId = $response['current_node'];
            } else {
                $conversationId = null;
                $parentId = (string) Uuid::uuid4();
            }
        }

        $data = [
            'action' => 'next',
            'messages' => [
                [
                    'id' => (string) Uuid::uuid4(),
                    'role' => 'user',
                    'author' => ['role' => 'user'],
                    'content' => ['content_type' => 'text', 'parts' => [$prompt]],
                ],
            ],
            'conversation_id' => $conversationId,
            'parent_message_id' => $parentId,
            'model' => empty($this->accounts[$account]['model']) ? $this->accounts[$account]['paid'] ? 'text-davinci-002-render-paid' : 'text-davinci-002-render-sha' : $this->accounts[$account]['model'],
        ];

        try {
            $response = $this->http->post(
                'conversation',
                [
                    'json' => $data,
                    'headers' => [
                        'Authorization' => $token,
                        'Accept' => 'text/event-stream',
                        'Content-Type' => 'application/json',
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36 Edg/110.0.1587.63',
                        'X-Openai-Assistant-App-Id' => '',
                        'Connection' => 'close',
                        'Accept-Language' => 'en-US,en;q=0.9',
                        'Referer' => 'https://chat.openai.com/chat',
                    ],
                    'stream' => true,
                ]
            );
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                throw new Exception(Psr7\Message::toString($e->getResponse()));
            } else {
                throw new Exception($e->getMessage());
            }
        }

        $answer = '';
        $conversationId = '';
        $messageId = '';
        $model = '';

        if ($stream) {
            $data = $response->getBody();
            while (! $data->eof()) {
                $raw = Psr7\Utils::readLine($data);
                $line = self::formatStreamMessage($raw);
                if (self::checkFields($line)) {
                    $answer = $line['message']['content']['parts'][0];
                    $conversationId = $line['conversation_id'] ?? null;
                    $messageId = $line['message']['id'] ?? null;
                    $model = $line["message"]["metadata"]["model_slug"] ?? null;

                    yield [
                        "answer" => $answer,
                        "id" => $messageId,
                        'conversation_id' => $conversationId,
                        "model" => $model,
                        "account" => $account,
                    ];
                }
                unset($raw, $line);
            }
        } else {
            foreach (explode("\n", $response->getBody()) as $line) {
                $line = trim($line);
                if ($line === 'Internal Server Error') {
                    throw new Exception($line);
                }
                if ($line === '') {
                    continue;
                }

                $line = $this->formatStreamMessage($line);

                if (! $this->checkFields($line)) {
                    if (isset($line["detail"]) && $line["detail"] === "Too many requests in 1 hour. Try again later.") {
                        throw new Exception("Rate limit exceeded");
                    }
                    if (isset($line["detail"]) && $line["detail"] === "Conversation not found") {
                        throw new Exception("Conversation not found");
                    }
                    if (isset($line["detail"]) && $line["detail"] === "Something went wrong, please try reloading the conversation.") {
                        throw new Exception("Something went wrong, please try reloading the conversation.");
                    }
                    if (isset($line["detail"]) && $line["detail"] === "invalid_api_key") {
                        throw new Exception("Invalid access token");
                    }
                    if (isset($line["detail"]) && $line["detail"] === "invalid_token") {
                        throw new Exception("Invalid access token");
                    }

                    continue;
                }

                if ($line['message']['content']['parts'][0] === $prompt) {
                    continue;
                }

                $answer = $line['message']['content']['parts'][0];
                $conversationId = $line['conversation_id'] ?? null;
                $messageId = $line['message']['id'] ?? null;
                $model = $line["message"]["metadata"]["model_slug"] ?? null;
            }

            yield [
                'answer' => $answer,
                'id' => $messageId,
                'conversation_id' => $conversationId,
                'model' => $model,
                'account' => $account,
            ];
        }
    }

    /**
     * getConversations
     *
     * @param  int  $offset
     * @param  int  $limit
     * @param  mixed  $account
     *
     * @return array
     * @throws Exception
     */
    public function getConversations(int $offset = 0, int $limit = 20, $account = 0): array
    {
        try {
            $token = $this->accessTokenToJWT($this->accounts[$account]['access_token']);
        } catch (Exception $e) {
            throw new Exception("Invalid account");
        }

        try {
            $response = $this->http->get('conversations', [
                'headers' => [
                    'Authorization' => $token,
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36 Edg/110.0.1587.63',
                    'Referer' => 'https://chat.openai.com/chat',
                ],
                'query' => [
                    'offset' => $offset,
                    'limit' => $limit,
                ],
            ])->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Response is not json');
        }

        if (! isset($data['items'])) {
            throw new Exception('Field missing');
        }

        return $data['items'];
    }

    /**
     * getConversationMessages
     *
     * @param  string  $conversationId
     * @param  mixed  $account
     *
     * @return array
     * @throws Exception
     */
    public function getConversationMessages(string $conversationId, $account = 0): array
    {
        try {
            $token = $this->accessTokenToJWT($this->accounts[$account]['access_token']);
        } catch (Exception $e) {
            throw new Exception("Invalid account");
        }

        try {
            $response = $this->http->get('conversation/'.$conversationId, [
                'headers' => [
                    'Authorization' => $token,
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36 Edg/110.0.1587.63',
                    'Referer' => 'https://chat.openai.com/chat',
                ],
            ])->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Response is not json');
        }

        return $data;
    }

    /**
     * generateConversationTitle
     *
     * @param  string  $conversationId
     * @param  string  $messageId
     * @param  mixed  $account
     *
     * @return bool
     * @throws Exception
     */
    public function generateConversationTitle(string $conversationId, string $messageId, $account = 0): bool
    {
        try {
            $token = $this->accessTokenToJWT($this->accounts[$account]['access_token']);
        } catch (Exception $e) {
            throw new Exception("Invalid account");
        }

        try {
            $response = $this->http->post('conversation/gen_title/'.$conversationId, [
                'headers' => [
                    'Authorization' => $token,
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36 Edg/110.0.1587.63',
                    'Referer' => 'https://chat.openai.com/chat',
                ],
                'json' => [
                    'message_id' => $messageId,
                    'model' => 'text-davinci-002-render',
                ],
            ])->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Response is not json');
        }

        if (isset($data['title'])) {
            return true;
        }

        return false;
    }

    /**
     * updateConversationTitle
     *
     * @param  string  $conversationId
     * @param  string  $title
     * @param  mixed  $account
     *
     * @return bool
     * @throws Exception
     */
    public function updateConversationTitle(string $conversationId, string $title, $account = 0): bool
    {
        try {
            $token = $this->accessTokenToJWT($this->accounts[$account]['access_token']);
        } catch (Exception $e) {
            throw new Exception("Invalid account");
        }

        try {
            $response = $this->http->patch('conversation/'.$conversationId, [
                'headers' => [
                    'Authorization' => $token,
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36 Edg/110.0.1587.63',
                    'Referer' => 'https://chat.openai.com/chat',
                ],
                'json' => [
                    'title' => $title,
                ],
            ])->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Response is not json');
        }

        if (isset($data['success']) && $data['success'] === true) {
            return true;
        }

        return false;
    }

    /**
     * deleteConversation
     *
     * @param  string  $conversationId
     * @param  mixed  $account
     *
     * @return bool
     * @throws Exception
     */
    public function deleteConversation(string $conversationId, $account = 0): bool
    {
        try {
            $token = $this->accessTokenToJWT($this->accounts[$account]['access_token']);
        } catch (Exception $e) {
            throw new Exception("Invalid account");
        }

        try {
            $response = $this->http->patch('conversation/'.$conversationId, [
                'headers' => [
                    'Authorization' => $token,
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36 Edg/110.0.1587.63',
                    'Referer' => 'https://chat.openai.com/chat',
                ],
                'json' => [
                    'is_visible' => false,
                ],
            ])->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Response is not json');
        }

        if (isset($data['success']) && $data['success'] === true) {
            return true;
        }

        return false;
    }

    /**
     * clearConversations
     *
     * @param  mixed  $account
     *
     * @return bool
     * @throws Exception
     */
    public function clearConversations($account = 0): bool
    {
        try {
            $token = $this->accessTokenToJWT($this->accounts[$account]['access_token']);
        } catch (Exception $e) {
            throw new Exception("Invalid account");
        }

        try {
            $response = $this->http->patch('conversations', [
                'headers' => [
                    'Authorization' => $token,
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36 Edg/110.0.1587.63',
                    'Referer' => 'https://chat.openai.com/chat',
                ],
                'json' => [
                    'is_visible' => false,
                ],
            ])->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage());
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Response is not json');
        }

        if (isset($data['success']) && $data['success'] === true) {
            return true;
        }

        return false;
    }

    /**
     * checkFields
     *
     * @param  mixed  $line
     *
     * @return bool
     */
    public function checkFields($line): bool
    {
        return isset($line['message']['content']['parts'][0])
            && isset($line['conversation_id'])
            && isset($line['message']['id']);
    }

    /**
     * formatStreamMessage
     *
     * @param  string  $line
     *
     * @return array|false
     */
    public function formatStreamMessage(string $line)
    {
        preg_match('/data: (.*)/', $line, $matches);
        if (empty($matches[1])) {
            return false;
        }

        $line = $matches[1];
        $data = json_decode($line, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        return $data;
    }

    /**
     * access_token To JWT
     *
     * @param  string  $accessToken
     *
     * @return string
     * @throws Exception
     */
    private function accessTokenToJWT(string $accessToken): string
    {
        try {
            $sAccessToken = explode(".", $accessToken);
            $sAccessToken[1] .= str_repeat("=", (4 - strlen($sAccessToken[1]) % 4) % 4);
            $dAccessToken = base64_decode($sAccessToken[1]);
            $dAccessToken = json_decode($dAccessToken, true);
        } catch (Exception $e) {
            throw new Exception("Access token invalid");
        }

        $exp = $dAccessToken['exp'] ?? null;
        if ($exp !== null && $exp < time()) {
            throw new Exception("Access token expired");
        }

        return 'Bearer '.$accessToken;
    }
}

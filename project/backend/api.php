<?php
// Set maximum execution time to 30 minutes (1800 seconds)
set_time_limit(1800);

// Define the ChatGPT class
class ChatGPT {
    // Private properties for API key, API endpoint, and maximum attempts
    private $apiKey;
    private $apiEndpoint = 'https://api.openai.com/v1/chat/completions';
    private $maxAttempts = 5;

    // Constructor to initialize API key
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    /**
     * Make a request to the ChatGPT API
     *
     * @param string $prompt The user's input prompt
     * @return string The model's response
     */
    public function generateResponse($prompt) {
        // Check if the API key is provided
        if (empty($this->apiKey)) {
            $this->sendError(400, 'API key not provided.');
        }

        // Check if the prompt is empty
        if (empty($prompt)) {
            $this->sendError(400, 'Input prompt is empty.');
        }

        // Prepare request data
        // https://platform.openai.com/docs/api-reference/chat/create
        $requestData = [
            'model' => 'gpt-3.5-turbo',
            // GPT 4:   gpt-4-0125-preview, gpt-4-turbo-preview, gpt-4-1106-preview, gpt-4-vision-preview, gpt-4, gpt-4-0613, gpt-4-32k, gpt-4-32k-0613
            // GPT 3.5: gpt-3.5-turbo-0125, gpt-3.5-turbo, gpt-3.5-turbo-1106, gpt-3.5-turbo-instruct, gpt-3.5-turbo-16k, gpt-3.5-turbo-0613, gpt-3.5-turbo-16k-0613
            'messages' => [
                // This system message informs the model about its role in the conversation.
                // Including a system message can influence the overall tone and style of the responses generated by the model.
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant.'
                ], [
                    'role' => 'user',
                    'content' => $prompt
                ]],
        ];

        // Attempt API requests with a maximum limit
        $attempts = 0;
        do {
            // Make API request
            $response = $this->makeApiRequest($requestData);

            // Check if the response is successful
            if ($response && isset($response['choices'][0]['message']['content'])) {
                $response = json_decode($response['choices'][0]['message']['content'], true);

                // If the response is null, continue the loop
            } else {
                // If there's an error in the response, retrieve and send an appropriate error message
                $errorMessage = isset($response['error']['message']) ? $response['error']['message'] : 'Unknown error';
                $this->sendError(500, $errorMessage);
            }

            $attempts++;
        } while (empty($response) && $attempts < $this->maxAttempts); // Repeat the loop until a non-null response or maxAttempts is reached

        return $response;
    }

    /**
     * Send an error response with a JSON-encoded message and the specified HTTP status code.
     *
     * @param int    $statusCode The HTTP status code for the error response.
     * @param string $message    The error message to be included in the JSON response.
     *
     * @return void Exits the script after sending the error response.
     */
    private function sendError($statusCode, $message) {
        header('Content-Type: application/json', true, $statusCode);
        echo json_encode(['error' => $message]);
        exit();
    }

    /**
     * Make a request to the ChatGPT API
     *
     * @param array $data The data to send in the API request
     * @return array|false The decoded JSON response or false on failure
     */
    private function makeApiRequest($data) {
        // Prepare headers
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];

        // Initialize cURL session
        $ch = curl_init($this->apiEndpoint);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute cURL session and get the response
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            return false;
        }

        // Decode the JSON response
        $decodedResponse = json_decode($response, true);

        // Close cURL session
        curl_close($ch);

        return $decodedResponse;
    }
}
// Allow CORS if applicable
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 1000');
}
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
    }

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: Accept, Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, Authorization");
    }
    exit(0);
}

// Initialize API key and ChatGPT object
$apiKey = 'secret';
$chatGPT = new ChatGPT($apiKey);

// Retrieve prompt from input
$prompt = json_decode(file_get_contents("php://input"),true)['prompt'];

// Define the message template for generating a course response
$message = 'I need a written course on "{$prompt}". Do not include things like: videos, certificats, downloadable content. Answer needs to have those sections in JSON format where string in " is an JSON atribute: "course_name" - needs to be inviting and with a goal in mind, "keywords" - 10 good Google SEO keywords, "abstract" - maximum of 300 characters, "whats_included" - text about what is included in a course, "included" - 10 main titles from the course (output it like this {{"title"},{"title"}}), "prerequisites" - required skills and experiences, "syllabus" - what will students be able to do after a course, "readings" - additional 5 materials (format it like {"title","author"}), "faq" - 10 questions and answers on faq about the course (format it like {"q","a"}), "task" - practical task that reader can do based on an course (format it like {"title","instructions","learned"}). Answer with nothing but JSON.';

// Generate response from ChatGPT based on the message template
$response = $chatGPT->generateResponse(str_replace('{$prompt}', $prompt, $message));

// Check if the response includes course details
if(empty($response['included'])) {
    // Send error response if course details are not found
    $this->sendError(500, 'API Problems');
} else {
    // Generate content for each included title
    foreach ($response['included'] as $i => $c) {
        // Define the content prompt for each title
        $content_prompt = 'Answer with a text about "{prompt}" based on this title (format answer in JSON like this {"title","content", "quiz":[{"question", "answer", "learned"}], "keywords" - Best Google SEO keywords based on a content formated in 1 string, "suggested_lesson_type" - this can be video or article or task}): ' . json_encode($c) . ' . Quiz should be based on content. In quiz section there should be 5 questions, answers and lessions learned.';

        // Generate response for content prompt
        $response['content'][$i] = $chatGPT->generateResponse(str_replace('{$prompt}', $prompt, $content_prompt));
    }


    // Set header for JSON response
    header('Content-Type: application/json');

    // Output the JSON response
    echo json_encode($response);
}

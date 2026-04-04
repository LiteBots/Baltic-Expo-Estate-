<?php
// Ustawienie nagłówków, aby React mógł swobodnie komunikować się z tym plikiem
header('Content-Type: application/json; charset=utf-8');

// Akceptujemy tylko żądania typu POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Odbieramy dane JSON z Reacta
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);

    // Zabezpieczenie danych (usuwamy tagi HTML, białe znaki)
    $nip = isset($input['nip']) ? htmlspecialchars(trim($input['nip'])) : '';
    $companyName = isset($input['companyName']) ? htmlspecialchars(trim($input['companyName'])) : '';
    $name = isset($input['name']) ? htmlspecialchars(trim($input['name'])) : '';
    $phone = isset($input['phone']) ? htmlspecialchars(trim($input['phone'])) : '';
    $email = isset($input['email']) ? filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL) : '';

    // Podstawowa walidacja (upewniamy się, że wymagane pola nie są puste)
    if (empty($companyName) || empty($name) || empty($phone) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400); // Bad Request
        echo json_encode(["status" => "error", "message" => "Brakujące lub nieprawidłowe dane."]);
        exit;
    }


    $to = "kontakt@balticexpo.pl";
    $subject = "Nowe zgłoszenie: $companyName - Bałtyckie Targi Nieruchomości";

   
    $message = "Otrzymano nowe zgłoszenie od wystawcy:\n\n";
    $message .= "Firma: $companyName\n";
    $message .= "NIP: " . ($nip ? $nip : "Brak podanego NIP-u") . "\n";
    $message .= "Osoba kontaktowa: $name\n";
    $message .= "Telefon: $phone\n";
    $message .= "E-mail służbowy: $email\n";

    
    $headers = "From: no-reply@balticexpo.pl\r\n"; 
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

  
    if (mail($to, $subject, $message, $headers)) {
        http_response_code(200); 
        echo json_encode(["status" => "success", "message" => "Wiadomość wysłana pomyślnie."]);
    } else {
        http_response_code(500); 
        echo json_encode(["status" => "error", "message" => "Błąd serwera. Wiadomość nie została wysłana."]);
    }
} else {
    http_response_code(405); 
    echo json_encode(["status" => "error", "message" => "Niedozwolona metoda żądania."]);
}
?>

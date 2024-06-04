<?php
include 'connect.php';

session_start();

// Vérifie si le score est déjà enregistré dans la session
if (!isset($_SESSION['score'])) {
    $_SESSION['score'] = 0; // initialise le score à zéro s'il n'existe pas encore
}

// Initialise le tableau des questions déjà répondues s'il n'existe pas encore dans la session
if (!isset($_SESSION['answered_questions'])) {
    $_SESSION['answered_questions'] = [];
}

$sql = "SELECT * FROM questions_quiz";
$stmt = $db->query($sql);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// réinitialise le score et les questions répondues si le quiz est réinitialisé
if (isset($_GET['reset']) && $_GET['reset'] == 1) {
    $_SESSION['score'] = 0;
    $_SESSION['answered_questions'] = [];
    // redirige vers la même page pour que la session soit détruite après l'affichage de la page
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// détruit la session si la variable de session de réinitialisation du score est définie
if (isset($_SESSION['reset_score']) && $_SESSION['reset_score']) {
    $_SESSION['score'] = 0;
    $_SESSION['answered_questions'] = [];
    unset($_SESSION['reset_score']); // supprime la variable de session de réinitialisation
    session_destroy(); 
    header("Location: ".$_SERVER['PHP_SELF']); // redirige vers la même page pour supprimer les paramètres GET
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {
    $question_id = $_POST['question_id'];
    $selected_answer = $_POST['answer'];

    // Vérifier si la réponse pour cette question a déjà été soumise
    if (in_array($question_id, $_SESSION['answered_questions'])) {
        // Retourne un message indiquant que la question a déjà été répondue
        echo json_encode(['feedback' => "Cette question a déjà été répondue.", 'score' => $_SESSION['score']]);
        exit();
   }
    // récupère la bonne réponse pour la question soumise
    $sql = "SELECT bonne_reponse FROM questions_quiz WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $question_id, PDO::PARAM_INT);
    $stmt->execute();
    $correct_answer = $stmt->fetchColumn();

    // Détermine le feedback
    $feedback = ($selected_answer == $correct_answer) ? "Bonne réponse!" : "Mauvaise réponse. La bonne réponse était : $correct_answer";

     // Incrémente le score si la réponse est correcte
     if ($selected_answer == $correct_answer) {
        $_SESSION['score']++;
    }

    // Stocker que la réponse a été soumise pour cette question
    $_SESSION['answered_questions'][] = $question_id;

     // Retourner le feedback et le score en JSON
     echo json_encode(['feedback' => $feedback, 'score' => $_SESSION['score']]);
     exit();
 }

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <title>Quiz</title>
    <script>
    function submitForm(event, form) {
        event.preventDefault();
        const formData = new FormData(form);
        formData.append('ajax', '1');

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const feedbackElement = form.querySelector('.feedback');
            feedbackElement.innerHTML = `<strong>${data.feedback}</strong>`;

            // Désactiver les options de réponse
            const radioButtons = form.querySelectorAll('input[type="radio"]');
            radioButtons.forEach(radioButton => {
                radioButton.disabled = true;
            });

            // Mettre à jour le score
            const scoreElement = document.getElementById('score');
            scoreElement.innerHTML = `Score: ${data.score}`;
        })
        .catch(error => console.error('Error:', error));
    }
    </script>
</head>
<body>
    <h1>SUPER INTERGALACTICAL SPACE QUIZ</h1>
    <div id="score">
      <img src="images/score.svg">
        Score: <?php echo isset($_SESSION['score']) ? $_SESSION['score'] : 0; ?>
    </div>
        <?php
         if (!empty($questions)) {
            foreach ($questions as $row) {
                $question_id = $row['id'];
                $question = $row['question'];
                $bonne_reponse = $row['bonne_reponse'];
                $mauvaise_reponse1 = $row['mauvaise_reponse1'];
                $mauvaise_reponse2 = $row['mauvaise_reponse2'];
                $image = $row["image"];

                // mélange les réponses
                $answers = array($bonne_reponse, $mauvaise_reponse1, $mauvaise_reponse2);
                shuffle($answers);
        ?>
            <form method="post" onsubmit="submitForm(event, this)">
                <p><?php echo $question; ?></p>
                <img src="images/<?php echo $image; ?>">
                <?php foreach ($answers as $answer) { ?>
                    <input type="radio" name="answer" value="<?php echo $answer; ?>" required>
                    <label><?php echo $answer; ?></label><br>
                <?php } ?>
                <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                <input type="submit" value="Valider">
                <div class="feedback"></div>
            </form>
        <?php
            }
        } else {
            echo "0 résultats";
        }
        ?>
        <!-- Formulaire de réinitialisation -->
    <form method="get">
        <input type="hidden" name="reset" value="1">
        <input type="submit" value="Réinitialiser le quiz">
    </form>
</body>
</html>

<!-- <?php
$conn->close();
?> -->
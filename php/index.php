<?php
include 'connect.php';

// récupère les questions depuis la base de données
$sql = "SELECT * FROM questions_quiz";
$stmt = $db->query($sql);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_id = $_POST['question_id'];
    $selected_answer = $_POST['answer'];

    // récupère la bonne réponse pour la question soumise
    $sql = "SELECT bonne_reponse FROM questions_quiz WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $question_id, PDO::PARAM_INT);
    $stmt->execute();
    $correct_answer = $stmt->fetchColumn();

    if ($selected_answer == $correct_answer) {
        $feedback[$question_id] = "Bonne réponse!";
    } else {
        $feedback[$question_id] = "Mauvaise réponse";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz</title>
</head>
<body>
    <h1>Quiz</h1>
        <?php
         if (!empty($questions)) {
            foreach ($questions as $row) {
                $question_id = $row['id'];
                $question = $row['question'];
                $bonne_reponse = $row['bonne_reponse'];
                $mauvaise_reponse1 = $row['mauvaise_reponse1'];
                $mauvaise_reponse2 = $row['mauvaise_reponse2'];

                // mélange les réponses
                $answers = array($bonne_reponse, $mauvaise_reponse1, $mauvaise_reponse2);
                shuffle($answers);
        ?>
            <form method="post" action="">
                <p><?php echo $question; ?></p>
                <?php foreach ($answers as $answer) { ?>
                    <input type="radio" name="answer" value="<?php echo $answer; ?>" required>
                    <label><?php echo $answer; ?></label><br>
                <?php } ?>
                <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                <input type="submit" value="Soumettre">
            </form>
            <?php
            // afficher le feedback pour cette question
            if (isset($feedback[$question_id])) {
                echo "<p><strong>{$feedback[$question_id]}</strong></p>";
            }
            ?>
        <?php
            }
        } else {
            echo "0 résultats";
        }
        ?>
</body>
</html>

<!-- <?php
$conn->close();
?> -->
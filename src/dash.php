<?php

session_start();
include 'db.php';

if (isset($_POST['logout'])) {
  $_SESSION = array();

  session_destroy();

  header("Location: index.php");
  exit();
}


function saveInputToDb($text, $cipher, $action){

  global $conn;

  $userId = $_SESSION['user_id'];
   
  $stmt = $conn->prepare("INSERT INTO inputs(user_id, input_text, cipher, action)  
                      VALUES(?, ?, ?, ?)");
   
  $stmt->bind_param("isss", $userId, $text, $cipher, $action);
   
  if($stmt->execute()) {
    echo "";
  }
  else {
    echo "Error inserting: " . $conn->error;
  }
}

function substitutionCipher($text, $key, $mode) {
  
  if($mode == 'encrypt') {
    $upperAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $lowerAlphabet = "abcdefghijklmnopqrstuvwxyz";
    $encryptedText = '';

    for ($i = 0; $i < strlen($text); $i++) {
        $char = $text[$i];
        if (ctype_upper($char)) { $pos = strpos($upperAlphabet, $char);
            if ($pos !== false) { $encryptedText .= strtoupper($key[$pos]); } else {  $encryptedText .= $char; }
        } else {  $pos = strpos($lowerAlphabet, $char);
            if ($pos !== false) { $encryptedText .= strtolower($key[$pos]);
            } else {
                $encryptedText .= $char;} } }
    return $encryptedText;

  } else {
    $upperAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $lowerAlphabet = "abcdefghijklmnopqrstuvwxyz";
    $decryptedText = '';

    for ($i = 0; $i < strlen($text); $i++) {
        $char = $text[$i];
        if (ctype_upper($char)) {
            $pos = strpos(strtoupper($key), $char);
            if ($pos !== false) { $decryptedText .= $upperAlphabet[$pos];
            } else {  $decryptedText .= $char; }
        } else {
            $pos = strpos(strtolower($key), $char);
            if ($pos !== false) { $decryptedText .= $lowerAlphabet[$pos]; } else { $decryptedText .= $char; }
        }}
    return $decryptedText;
  } 
  
}

function encryptDoubleTransposition($text, $keyString) {
    return transpositionCipher(transpositionCipher($text, $keyString, 'encrypt'), $keyString, 'encrypt');
  }
  
  function decryptDoubleTransposition($cipher, $keyString) {
    return transpositionCipher(transpositionCipher($cipher, $keyString, 'decrypt'), $keyString, 'decrypt');
  }
  
  function transpositionCipher($text, $keyString, $mode) {
    $key = array_map('intval', str_split($keyString));
    sort($key); // Sorting the key for reordering columns
    $numCols = count($key);
    $length = strlen($text);
    $numRows = ceil($length / $numCols);
    $text = str_pad($text, $numRows * $numCols, ' '); // Pad the text
    $grid = array_chunk(str_split($text), $numCols);
  
    if ($mode === 'encrypt') {
        $result = '';
        foreach ($key as $col) {
            foreach ($grid as $row) {
                $result .= $row[$col - 1];
            }
        }
    } else {
        $result = array_fill(0, $numRows, array_fill(0, $numCols, ''));
        $index = 0;
        foreach ($key as $col) {
            for ($row = 0; $row < $numRows; $row++) {
              $result[$row][$col - 1] = $text[$index++];
          }
      }
      $result = implode(array_map('implode', $result));
  }
  return $result;
}

function rc4Cipher($text, $key, $mode) {
  // Key-Scheduling Algorithm (KSA)
  $s = array();
  for ($i = 0; $i < 256; $i++) {
      $s[$i] = $i;
  }

  $j = 0;
  for ($i = 0; $i < 256; $i++) {
      $j = ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
      $temp = $s[$i];
      $s[$i] = $s[$j];
      $s[$j] = $temp;
  }

  // Pseudo-Random Generation Algorithm (PRGA)
  $i = $j = 0;
  $out = "";
  for ($y = 0; $y < strlen($text); $y++) {
      $i = ($i + 1) % 256;
      $j = ($j + $s[$i]) % 256;
      $temp = $s[$i];
      $s[$i] = $s[$j];
      $s[$j] = $temp;

      $k = $s[($s[$i] + $s[$j]) % 256];
      $c = ord($text[$y]) ^ $k;
      $out .= chr($c);
  }

  // Encoding for encryption and decoding for decryption
  if ($mode == "encrypt") {
      // Encode the output with Base64 for encryption
      return base64_encode($out);
  } else if ($mode == "decrypt") {
      // Decode the input with Base64 for decryption
      return rc4Cipher(base64_decode($text), $key, 'process');
  } else if ($mode == 'process') {
      // Internal processing for the actual decryption
      return $out;
  }

  return $out;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Encryption Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        form {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 0 auto;
        }

        h3 {
            color: #333;
        }

        textarea, select, input[type="text"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .output {
            background-color: #fff;
            padding: 15px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 20px auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
    <script type="text/javascript">
        function toggleInputMethod() {
            var textInput = document.getElementById("text-input");
            var fileInput = document.getElementById("file-input");
            var toggleBtn = document.getElementById("toggle-btn");

            if (textInput.style.display === "none") {
                textInput.style.display = "block";
                fileInput.style.display = "none";
                toggleBtn.textContent = "Use File Upload";
            } else {
                textInput.style.display = "none";
                fileInput.style.display = "block";
                toggleBtn.textContent = "Use Text Input";
            }
        }

        window.onload = function() {
            // Initialize to show text input first
            toggleInputMethod();
        };
    </script>
</head>
<body>
<?php if (isset($_SESSION['user_id'])): ?>
    <!-- Logout Button -->
    <form action="dash.php" method="post">
        <button type="submit" name="logout">Logout</button>
    </form>
<?php endif; ?>
<form method="post" enctype="multipart/form-data">
    <button type="button" id="toggle-btn" onclick="toggleInputMethod()">Use File Upload</button>

    <div id="text-input">
        <h3>Input Text</h3>
        <textarea name="text" placeholder="Enter text to encrypt/decrypt"></textarea>
    </div>

    <div id="file-input" style="display: none;">
        <h3>Upload a Text File (.txt only)</h3>
        <input type="file" name="file" accept=".txt">
    </div>
    <h3>Encryption/Decryption Settings</h3>
    <select name="action">
        <option value="encrypt">Encrypt</option>
        <option value="decrypt">Decrypt</option>
    </select>

    <select name="cipher">
        <option value="substitution">Simple Substitution Cipher</option>
        <option value="transposition">Double Transposition Cipher</option>
        <option value="rc4">RC4 Cipher</option>
    </select>

    <h3>Key </h3>
    <input type="text" name="key" placeholder="Enter key">

    <button type="submit">Submit</button>
</form>
<?php

// if($_SERVER['REQUEST_METHOD'] == 'POST') {

//   if(isset($_POST['file'])) {

//     echo "file is uploaded";
//     $fileName = $_FILES['file']['name'];
//     $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

//     if($fileType == "txt") {

//       $plainText = file_get_contents($file);
//       $cipher = $_POST['cipher'];
//       $action = $_POST['action'];
//       $key = $_POST['key'];

//       if($cipher == "substitution") {
//         $output = substitutionCipher($text, $key, $action);
//       }
//       else if($cipher == "transposition") {
//         $output = transpositionCipher($text, $key, $action);
//       } 
//       else if($cipher == "rc4") {
//         $output = rc4Cipher($text, $key, $action);
//         // $output = rc4Cipher($text, $key, $action);
//         if($action=='encrypt'){
//           $output = base64_decode($output);
//           echo "output is $output \n";
//         }
//       }

//     }
//   }
//   else{
//     $text = $_POST['text'];
//     $cipher = $_POST['cipher'];
//     $action = $_POST['action'];
//     $key = $_POST['key'];

//     $text = $_POST['text'];
//     $text = filter_var($text, FILTER_SANITIZE_SPECIAL_CHARS);

//     if($cipher == "substitution") {      
//       $output = substitutionCipher($text, $key, $action);
//       saveInputToDb($text, $cipher, 'decrypt');
//     }
//     else if($cipher == "transposition") {
//       $output = transpositionCipher($text, $key, $action);
//     } 
//     else if($cipher == "rc4") {
//       $output = rc4Cipher($text, $key, $action);

//     }
//     saveInputToDb($text, $cipher, $action);
//   }
// }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  $cipher = $_POST['cipher'];
  $action = $_POST['action'];
  $key = $_POST['key'];
  $text = '';

  // Check if a file is uploaded
  if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
      $fileName = $_FILES['file']['name'];
      $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

      // Validate file type
      if ($fileType == "txt") {
          $text = file_get_contents($_FILES['file']['tmp_name']);
      } else {
          echo "Please upload a .txt file.";
          exit;
      }
  } elseif (!empty($_POST['text'])) {
      // Get text from the textarea if no file is uploaded
      $text = $_POST['text'];
      $text = filter_var($text, FILTER_SANITIZE_SPECIAL_CHARS);
  }

  // Process the text with the selected cipher
  if (!empty($text)) {
      if ($cipher == "substitution") {
          $output = substitutionCipher($text, $key, $action);
      } elseif ($cipher == "transposition") {
          $output = ($action == 'encrypt') ? encryptDoubleTransposition($text, $key) : decryptDoubleTransposition($text, $key);
      } elseif ($cipher == "rc4") {
          $output = rc4Cipher($text, $key, $action);
      }

      if (isset($_SESSION['user_id'])) {
        // Only save to DB if user is logged in
        saveInputToDb($text, $cipher, $action);
      }
  } else {
      echo "No input provided.";
  }
}
 if (isset($output) && !empty($output)): ?>
    <div class="output">
        <strong>Output:</strong> <?php echo htmlspecialchars($output); ?>
    </div>
<?php 
endif;


function displayUserActions($userId, $conn) {
  $stmt = $conn->prepare("SELECT input_text, cipher, action, created_at FROM inputs WHERE user_id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
      echo "<table border='1'><tr><th>Input Text</th><th>Cipher</th><th>Action</th><th>Timestamp</th></tr>";
      while ($row = $result->fetch_assoc()) {
          echo "<tr><td>" . htmlspecialchars($row["input_text"]) . "</td><td>" . $row["cipher"] . "</td><td>" . $row["action"] . "</td><td>" . $row["created_at"] . "</td></tr>";
      }
      echo "</table>";
  } else {
      echo "No records found.";
  }
}

if (isset($_SESSION['user_id'])) {
  displayUserActions($_SESSION['user_id'], $conn);
}
?>

</body>
</html>
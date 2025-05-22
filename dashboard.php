<?php
session_start();
if (!isset($_SESSION['access_token']) || !isset($_SESSION['linkedin_id'])) {
    header("Location: login.php");
    exit;
}
ini_set('memory_limit', '512M');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Share on LinkedIn</title>
    <link rel="stylesheet" href="./assets/styles.css">
    <style>
        #progressBar {
            width: 100%;
            background-color: #f3f3f3;
            border: 1px solid #ccc;
            margin-top: 10px;
            display: none;
        }
        #progressBar div {
            height: 20px;
            background-color: #4CAF50;
            width: 0%;
            text-align: center;
            color: white;
        }
    </style>
</head>
<body>
<h2>Welcome <?php echo $_SESSION['userData']['name'] ?? "" ?>, Share a Post <a href="logout.php">Logout</a></h2>

<form id="shareForm" enctype="multipart/form-data">
    <label>Post Text</label>
    <textarea name="post_text" required placeholder="Write a post.."></textarea>

    <label>Upload Image (optional)</label>
    <input type="file" name="image_file" accept="image/*" />

    <label>Upload Video (optional)</label>
    <input type="file" name="video_file" accept="video/*" />

    <button type="submit">Share</button>
</form>

<div id="progressBar"><div></div></div>
<div id="responseBox" style="margin-top:20px; font-weight: bold;"></div>

<script>
document.getElementById('shareForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const imageFile = form.querySelector('input[name="image_file"]').files[0];
    const videoFile = form.querySelector('input[name="video_file"]').files[0];

    const maxFileSize = 6 * 1024 * 1024;

    if (imageFile && imageFile.size > maxFileSize) {
        alert("❌ Image file exceeds the 6MB limit.");
        return;
    }
    if (videoFile && videoFile.size > maxFileSize) {
        alert("❌ Video file exceeds the 5MB limit.");
        return;
    }

    const formData = new FormData(form);
    const progressBar = document.getElementById('progressBar');
    const progress = progressBar.querySelector('div');
    const responseBox = document.getElementById('responseBox');

    const xhr = new XMLHttpRequest();

    xhr.upload.addEventListener("progress", function(e) {
        if (e.lengthComputable) {
            const percent = (e.loaded / e.total) * 100;
            progressBar.style.display = "block";
            progress.style.width = percent + "%";
            progress.innerText = Math.round(percent) + "%";
        }
    });

    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            progressBar.style.display = "none";
            try {
                const result = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && result.success === true) {
                    responseBox.innerHTML = "✅ Post shared successfully!";
                    form.reset();
                } else {
                    responseBox.innerHTML = "❌ Error: " + (result.error || "Unknown error occurred.");
                }
            } catch (err) {
                responseBox.innerHTML = "❌ Invalid server response.";
                console.error(err);
            }
        }
    };

    xhr.open("POST", "http://localhost:8888/linkedin-php-integration/api/share.php", true);
    xhr.send(formData);
});
</script>

</body>
</html>

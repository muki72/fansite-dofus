document.addEventListener("DOMContentLoaded", function () {
   
    const upvoteButtons = document.querySelectorAll(".upvote");
    const downvoteButtons = document.querySelectorAll(".downvote");

    upvoteButtons.forEach(button => {
        button.addEventListener("click", function () {
            console.log("Upvote");
            sendVote(this.dataset.postId, "up");
        });
    });

    downvoteButtons.forEach(button => {
        button.addEventListener("click", function () {
            console.log("Downvote");
            sendVote(this.dataset.postId, "down");
        });
    });

    //  envoye du vote via Fetch
    //  prend en param l'id du post ciblé et le type de vote envoyer ▲ ou ▼

    function sendVote(postId, voteType) {
        //  requête AJAX à l'URL
        fetch(`/post/${postId}/vote`, {
            method: "POST",
            headers: {
                //  envoie des données structurées au format JSON
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ vote: voteType })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                //  <p> qui affiche le score (#score-{postId}) dans post/index.html.twig
                let scoreElement = document.querySelector(`#score-${postId}`);

                //  Met à jour l'affichage
                scoreElement.textContent = data.newScore; 
            } else {
                console.error("Erreur lors du vote");
            }
        })
        .catch(error => console.error("Erreur réseau :", error));
    }
});

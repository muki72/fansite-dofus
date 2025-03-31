document.addEventListener("DOMContentLoaded", function () {
    

    const upvoteButtons = document.querySelectorAll(".upvote");
    const downvoteButtons = document.querySelectorAll(".downvote");

    upvoteButtons.forEach(button => {
        button.addEventListener("click", function () {
            let parent = this.closest(".post-container");
            let downvoteButton = parent.querySelector(".downvote");

            // Vérifie si l'upvote est déjà actif (annulation)
            let isActive = this.classList.contains("active-up-vote");
            let voteValue = isActive ? 0 : 1; // 0 pour annuler, 1 pour upvote

            sendVote(this.dataset.postId, voteValue);

            // Reset classes
            this.classList.toggle("active-up-vote", !isActive);
            downvoteButton.classList.remove("active-down-vote");
        });
    });

    downvoteButtons.forEach(button => {
        button.addEventListener("click", function () {
            let parent = this.closest(".post-container");
            let upvoteButton = parent.querySelector(".upvote");

            // Vérifie si le downvote est déjà actif (annulation)
            let isActive = this.classList.contains("active-down-vote");
            let voteValue = isActive ? 0 : -1; // 0 pour annuler, -1 pour downvote

            sendVote(this.dataset.postId, voteValue);

            // Reset classes
            this.classList.toggle("active-down-vote", !isActive);
            upvoteButton?.classList.remove("active-up-vote");
        });
    });

    //  envoye du vote via Fetch
    //  prend en param l'id du post ciblé et le type de vote envoyer ▲ ou ▼
    function sendVote(postId, voteType) {
        fetch(`/post/${postId}/vote`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ vote: voteType })
        })
            .then(response => response.json())
            .then(data => {


                //  <p> qui affiche le score (#score-{postId}) dans post/index.html.twig
                let scoreElement = document.querySelector(`#score-${postId}`);

                //  Met à jour l'affichage
                scoreElement.textContent = data.newScore;



            })
            .catch(error => console.error("Erreur réseau :", error));
    }
});

window.onload = () => {
    //Gestion des boutons "supprimer"

    let links = document.querySelectorAll("[data-delete]")
    //on boucle sur links
    for(link of links){
        //ecoute le click
        link.addEventListener("click", function(e){
            //empecher la navigation
            e.preventDefault()

            //on demande une confirmation
            if(confirm("Voulez-vous supprimer cette image ?")){
                //on envoie une requete Ajax vers le href du lien avec la méthode delete
                fetch(this.getAttribute("href"), {
                    method: 'DELETE',
                    headers: { "X-Requested-With": "XMLHttpRequest",
                    "Content-Type": "application/json"
                    },
                    body: JSON.stringify({"_token": this.dataset.token})
                }).then(
                    //on recupère la reponse en JSON
                    response => response.json()                    
                ).then(data => {
                    if(data.succes){
                        this.parentElement.remove()
                    }else {
                        alert(data.error)
                    }
                }).catch(e => alert(e))
            }
        })
    }
}
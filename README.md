# Projet "Réservations"

## Install Docker

    cd Docker
    docker build -t bruno:reservation .
    docker run --name reservation -v D:\chico\OneDrive\Documents\iDalgo\Project\bruno\Src:/usr/share/nginx/html:ro -d -p 8001:80 idalgo:bo-web

### Docker misc

    docker rmi bruno:reservation
    docker rmi $(docker images -q) -f
    
    docker stop reservation
    docker rm reservation
    docker rm $(docker ps -a -q)
    
    docker exec -it reservation sh
    docker logs reservation

    Application
    Etude
    Prod
    Ticket (change)
    date
    fichier
    commentaire
    etat

# Generali - Projet Bruno : Réservations prod

## Install Docker

    cd Docker
    docker build -t fernando-conde:reservation .
    
    docker run --name reservation -v 
    D:\chico\OneDrive\Documents\iDalgo\Project\bruno\Src:/usr/share/nginx/html:ro -d -p 8001:80 fernando-conde:reservation

    docker run --name reservation -d -p 8004:80 fernando-conde:reservation

### Docker misc

    docker images
    docker rmi fernando-conde:reservation
    docker rmi $(docker images -q) -f
    
    docker ps -a
    docker stop reservation
    docker rm reservation
    docker rm $(docker ps -a -q)
    
    docker exec -it reservation sh
    docker logs reservation

## Install Local

### Requirement

You can also check the Docker/dockerfile firle settings

### Install

    git clone git@github.com:fernando-conde/reservation.git
    composer install

## Misc

    Application
    Etude
    Prod
    Ticket (change)
    date
    fichier
    commentaire
    etat

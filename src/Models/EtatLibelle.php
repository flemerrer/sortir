<?php

    namespace App\Models;

    enum EtatLibelle: string
    {
        case CREEE = 'Créée';
        case OUVERTE = 'Ouverte';
        case CLOTUREE = 'Clôturée';
        case EN_COURS = 'Activité en cours';
        case PASSEE = 'Passée';
        case ANNULEE = 'Annulée';
        case ARCHIVEE = "Archivée";
    }

# language: fr
Fonctionnalité: Gérer les droits par app

Scénario: La gestion des droits adm locale
    Etant donné que je suis authentifié en tant que "dubost_g" avec les roles "test_app_adm,student,prof"
    Quand       je fais un GET sur /restricted
    Alors       le status HTTP devrait être 200
    Et          je devrais avoir un résultat d'API en JSON
    Et          le résultat devrait être identique au JSON suivant :
    """
    {
        "id": 1,
        "login": "dubost_g",
        "logas": false,
        "login_date": "2013-08-13 14:42:42",
        "groups": [
            "adm",
            "student",
            "prof"
        ]
    }
    """

Scénario: Être close localement
    Etant donné que je suis authentifié en tant que "dubost_g" avec les roles "test_app_adm,test_app_close,student,prof"
    Quand       je fais un GET sur /restricted
    Alors       le status HTTP devrait être 403
    Et          je devrais avoir un résultat d'API en JSON
    Et          le résultat devrait être identique au JSON suivant :
    """
    "You are closed for this app !"
    """

Scénario: Faire une requête options
    Etant donné que je suis authentifié en tant que "dubost_g" avec les roles "test_app_adm,test_app_close,student,prof"
    Quand       je fais un OPTIONS sur /restricted
    Alors       le status HTTP devrait être 204

Scénario: Accéder à une zone ouverte
    Etant donné que je suis authentifié en tant que "dubost_g" avec les roles "test_app_adm,test_app_close,student,prof"
    Quand       je fais un GET sur /open
    Alors       le status HTTP devrait être 200
    Et          je devrais avoir un résultat d'API en JSON
    Et          le résultat devrait être identique au JSON suivant :
    """
    {
        "id": 1,
        "login": "dubost_g",
        "logas": false,
        "login_date": "2013-08-13 14:42:42",
        "groups": [
            "test_app_adm",
            "test_app_close",
            "student",
            "prof"
        ]
    }
    """

Scénario: Ne pas être connecté sur une zone protégée
    Quand       je fais un GET sur /restricted
    Alors       le status HTTP devrait être 401
    Et          je devrais avoir un résultat d'API en JSON
    Et          le résultat devrait être identique au JSON suivant :
    """
    "Authorization Required"
    """

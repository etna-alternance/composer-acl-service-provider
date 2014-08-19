# language: fr

@feature/auth @feature/redirect @GET
Fonctionnalité: Afin de savoir qui fait quoi,
                Toutes les actions sur /api/ doivent être authentifiée grace à authenticator.
                Par contre ce n'est pas obligatoire pour l'accès aux autres ressources.

@GET @prof @student @adm
Plan du Scénario: GET avec un cookie valide sur /api
    Etant donné que j'ai chargé l'acl-service-provider
    Et          setté le app['auth.app_name'] a "gsa"
    Etant donné que je suis authentifié en tant que "<user>" avec les roles "<role>"
    Quand       je fais un GET sur /api
    Et          le status HTTP devrait être 200
    Et          je devrais avoir un résultat d'API en JSON
    Et          le résultat devrait être identique au JSON suivant :
    """
    <json>
    """
    Exemples:
        | role                | user       | json |
        | adm,prof,student    | admin_1    | {"id": 1,"login": "admin_1","logas": false,"groups": [ "adm", "prof", "student"],"login_date": "2013-11-13 14:42:42"} |
        | gsa_adm             | admin_1    | {"id": 1,"login": "admin_1","logas": false,"groups": [ "adm"],"login_date": "2013-11-13 14:42:42"} |
        | gsa_adm,gsa_settings| admin_1    | {"id": 1,"login": "admin_1","logas": false,"groups": [ "adm", "settings"],"login_date": "2013-11-13 14:42:42"} |

@GET @prof @student @adm
Plan du Scénario: GET avec un cookie valide sur /api avec /pouet/* comme api_path
    Etant donné que j'ai chargé l'acl-service-provider
    Et          setté le app['auth.app_name'] a "gsa"
    Et          setté le app['auth.api_path'] a "/pouet/*"
    Etant donné que je suis authentifié en tant que "<user>" avec les roles "<role>"
    Quand       je fais un GET sur /api
    Et          le status HTTP devrait être 200
    Et          je devrais avoir un résultat d'API en JSON
    Et          le résultat devrait être identique au JSON suivant :
    """
    <json>
    """
    Exemples:
        | role                | user       | json |
        | gsa_adm             | admin_1    | {"id": 1,"login": "admin_1","logas": false,"groups": [ "gsa_adm"],"login_date": "2013-11-13 14:42:42"} |
        | gsa_adm,gsa_settings| admin_1    | {"id": 1,"login": "admin_1","logas": false,"groups": [ "gsa_adm", "gsa_settings"],"login_date": "2013-11-13 14:42:42"} |

@GET @prof @student @adm
Plan du Scénario: GET avec un cookie valide sur /api
    Etant donné que j'ai chargé l'acl-service-provider
    Et          setté le app['auth.app_name'] a "auth"
    Etant donné que je suis authentifié en tant que "<user>" avec les roles "<role>"
    Quand       je fais un GET sur /api
    Et          le status HTTP devrait être 200
    Et          je devrais avoir un résultat d'API en JSON
    Et          le résultat devrait être identique au JSON suivant :
    """
    <json>
    """
    Exemples:
        | role                | user       | json |
        | adm,prof,student    | admin_1    | {"id": 1,"login": "admin_1","logas": false,"groups": [ "adm", "prof", "student"],"login_date": "2013-11-13 14:42:42"} |
        | auth_adm             | admin_1    | {"id": 1,"login": "admin_1","logas": false,"groups": [ "adm"],"login_date": "2013-11-13 14:42:42"} |
        | auth_adm,auth_settings| admin_1    | {"id": 1,"login": "admin_1","logas": false,"groups": [ "adm", "settings"],"login_date": "2013-11-13 14:42:42"} |


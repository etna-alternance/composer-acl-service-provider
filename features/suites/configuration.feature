# language: fr
Fonctionnalité: Configurer le bundle

Scénario: Configurer le bundle comme il faut
    Etant donné que je crée un nouveau kernel de test
    Quand       je configure le kernel avec le fichier "config/good.php"
    Et          je boot le kernel
    Alors       ca devrait s'être bien déroulé
    Et          je n'ai plus besoin du kernel de test

Scénario: Utiliser le bundle sans le AuthBundle
    Etant donné que j'utilise les bundles
    """
    [
        "\\ETNA\\Acl\\AclBundle"
    ]
    """
    Etant donné que je crée un nouveau kernel de test
    Quand       je configure le kernel avec le fichier "config/no_auth.php"
    Et          je boot le kernel
    Alors       ca ne devrait pas s'être bien déroulé
    Et          l'exception devrait avoir comme message "AclBundle requires AuthBundle"
    Et          je n'ai plus besoin du kernel de test
    Et          je remet les bundles par défaut

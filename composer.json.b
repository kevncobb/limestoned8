{
    "name": "vardot/varbase-project",
    "version": "dev-master",
    "version_normalized": "dev-master",
    "require": {
        "composer/installers": "~1.0",
        "cweagans/composer-patches": "~1.0",
        "drupal-composer/drupal-scaffold": "~2.0",
        "drupal/drupal-library-installer-plugin": "^0.3",
        "drupal/entity_clone": "1.x-dev#6d0ce053605e9aaf8412927a9b0ea8da7a9a06e5",
        "drupal/google_analytics_reports": "3.x-dev#2b6bb8efbc7f61ce3c1225638075aa6037b8db44",
        "drupal/image_resize_filter": "1.x-dev#c3f4b23b02005859092aaff746b9f21b794adc58",
        "drupal/l10n_client": "1.x-dev#9bf8d597732870bdca301512c71b6e5d74d48db2",
        "drupal/login_destination": "1.x-dev#54be8b89fdc073ca40af6b9b2eeb050e0aeb7908",
        "drupal/mail_edit": "1.x-dev#bcd0041830d8581b36e6211f0c8eabd8caf9652b",
        "drupal/menu_position": "1.x-dev#d134276b4bbd08b3c9678943d0225fbef7dd05b5",
        "drupal/node_edit_protection": "1.x-dev#902339c08222f838030c07aaea23bdc51ababebd",
        "drupal/security_review": "1.x-dev#35ebae445bb260e961e47c4c58efe7c50c228999",
        "drupal/tour_builder": "1.x-dev#d70e898949b7ec4095efb391a0dbec56d0117558",
        "oomphinc/composer-installers-extender": "~1.0",
        "vardot/varbase": "~8.6.0",
        "vardot/varbase-updater": "^1.0",
        "webflo/drupal-finder": "~1.0",
        "webmozart/path-util": "~2.0"
    },
    "conflict": {
        "drupal/drupal": "*"
    },
    "type": "project",
    "extra": {
        "_readme": [
            "NOTICE: We're now using composer patches from Vardot repository to suggest",
            "several fixes and better handling of patches in your Drupal project.",
            "You'll notice that we have included (https://github.com/vardot/composer-patches)",
            "in this composer.json repositories. This will replace the original",
            "library (cweagans/composer-patches) with our own from (vardot/composer-patches).",
            "See https://github.com/cweagans/composer-patches/pull/243 and more details",
            "on our changes on the composer-patches package. Once our changes get merged,",
            "we will revert to using (cweagans/composer-patches) without this override."
        ],
        "branch-alias": {
            "dev-8.x-6.x": "8.6.x-dev"
        },
        "installer-types": [
            "bower-asset",
            "npm-asset"
        ],
        "installer-paths": {
            "docroot/core": [
                "type:drupal-core"
            ],
            "docroot/profiles/{$name}": [
                "type:drupal-profile"
            ],
            "docroot/modules/contrib/{$name}": [
                "type:drupal-module"
            ],
            "docroot/themes/contrib/{$name}": [
                "type:drupal-theme"
            ],
            "docroot/libraries/slick": [
                "npm-asset/slick-carousel"
            ],
            "docroot/libraries/ace": [
                "npm-asset/ace-builds"
            ],
            "docroot/libraries/{$name}": [
                "type:drupal-library",
                "type:bower-asset",
                "type:npm-asset"
            ],
            "docroot/modules/custom/{$name}": [
                "type:drupal-custom-module"
            ],
            "docroot/themes/custom/{$name}": [
                "type:drupal-custom-theme"
            ],
            "drush/contrib/{$name}": [
                "type:drupal-drush"
            ]
        },
        "drupal-libraries": {
            "library-directory": "docroot/libraries",
            "libraries": [
                {
                    "name": "dropzone",
                    "package": "npm-asset/dropzone"
                },
                {
                    "name": "blazy",
                    "package": "npm-asset/blazy"
                },
                {
                    "name": "slick",
                    "package": "npm-asset/slick-carousel"
                },
                {
                    "name": "ace",
                    "package": "npm-asset/ace-builds"
                }
            ]
        },
        "enable-patching": true,
        "composer-exit-on-patch-failure": true,
        "patchLevel": {
            "drupal/core": "-p2"
        }
    },
    "scripts": {
        "drupal-scaffold": [
            "DrupalComposer\\DrupalScaffold\\Plugin::scaffold"
        ],
        "post-install-cmd": [
            "Varbase\\composer\\ScriptHandler::createRequiredFiles",
            "Varbase\\composer\\ScriptHandler::removeGitDirectories",
            "@composer drupal-scaffold"
        ],
        "post-update-cmd": [
            "Varbase\\composer\\ScriptHandler::createRequiredFiles",
            "Varbase\\composer\\ScriptHandler::removeGitDirectories"
        ],
        "post-drupal-scaffold-cmd": [
            "Varbase\\composer\\ScriptHandler::postDrupalScaffoldProcedure"
        ]
    },
    "license": [
        "GPL-2.0-or-later"
    ],
    "authors": [
        {
            "name": "Vardot",
            "homepage": "https://github.com/vardot",
            "role": "Maintainer"
        }
    ],
    "description": "Project template for Varbase distribution.",
    "repositories": {
        "drupal": {
            "type": "composer",
            "url": "https://packages.drupal.org/8"
        },
        "assets": {
            "type": "composer",
            "url": "https://asset-packagist.org"
        },
        "composer-patches": {
            "type": "vcs",
            "url": "https://github.com/vardot/composer-patches"
        },
        "packagist.org": {
            "type": "composer",
            "url": "https://packagist.org"
        }
    },
    "support": {
        "issues": "http://drupal.org/project/issues/varbase",
        "source": "http://cgit.drupalcode.org/varbase"
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
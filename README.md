About
=====
A simple library that automatically creates a local packagist for private repositories hosted on BitBucket.

The project has two main functions:

1. Creates a json file with auto-generated repositories (vcs) from a user or team on BitBucket that can be merged into an existing composer.json file. This is almost like having a local version of a Private Packagist.
2. Takes the auto-generated json file to also create a static composer repository (through [Satis]("https://github.com/composer/satis")) that can be uploaded to a server and served to other projects.

Installation
============

`composer require vincentholmblad/bitbucket-packagist`

Add these lines to composer.json after you've installed the package.

```json
// See https://bitbucket.org/account/user/<team or user>/api
// There you can create an OAuth consumer
// Make sure the consumer has access to read repositories
"config": {
    "bitbucket_consumer_key": "key",
    "bitbucket_consumer_secret": "secret",
    "bitbucket_team": "team or user"
},
// Merges scripts and repositories from the user or team
extra: {
    "merge-plugin": {
        "include": [
            "./vendor/vincentholmblad/bitbucket-packagist/bitbucket_packagist_base.json",
            "./bitbucket_packagist.json"
        ],
        "merge-scripts": true
    }
}
```

Then run `composer run-script bb_include_packages` whenver you need to update your local packagist.

If you want to automatically update the local packagist with any changes then also include this to your composer.json.

```json
// Automatically update local private packagist
"scripts": {
    "pre-update-cmd": [
        "@bb_include_packages"
    ],
    "pre-install-cmd": [
        "@bb_include_packages"
    ]
}
```

If you want to upload the result to a server then run `composer run-script bb_composer_packages` and upload the resulting folder *("./bitbucket_packagist_dist/")* to your hosting-service of choice. You can then include your new private packagist with this code in your composer.json.

```json
// Load repositores from private packagist
"repositories": [
    {
        "type": "composer",
        "url": "link to uploaded position"
    }
]
```

Sidenote for repositories
=========================

Make sure that every repository that you want to include has its own composer.json with at least a name and a version field. This namefield should match the name of the repository.

An example below.

```json
{
    "name": "your-shorthand/repo-name",
    "version": "master"
}
```
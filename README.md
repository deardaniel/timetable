The code for https://tt.daniel.ie/. This goes way back to 2006, when I thought regexes were the best way to parse HTML.

It no longer works.

## Run development server

```
$ php -S localhost:8000 -t public
```

## Access development server from mobile

```
$ ssh -R 80:localhost:8000 serveo.net
```

## Deploy site

```
$ ./deploy.sh
```

## crontab for module updater

```
TODO
```

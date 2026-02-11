# Run this after `heroku login` and after your repo is linked to an app (e.g. `heroku git:remote -a <app>`)

heroku config:set APP_KEY='base64:+L3SPKYLZ9CxQUdOuP0MdVa00z/hEDAGo6cFGN4wNGU='
heroku config:set APP_NAME='Simulasi_tka'
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false

# After deploying (or after importing a SQL dump), re-sync Postgres sequences to avoid duplicate PK errors:
# heroku run php artisan db:fix-pg-sequences

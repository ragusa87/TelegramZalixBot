# Telegram Fake Bot
 
This is used as an experiment.

This bot will proxy all messages to an administrator and when the administrator answers, it will be send back to the last user.

To be administrator:

 > /admin `Ymd`

To be a guest:
 
 > just speak. 

To leave as guest:
 
 > /stop
 
To leave as administrator:
  
 > /admin

## Demo
Use ZalixBot <https://web.telegram.org/#/im?p=@Zalix_Bot>

## Installation
Just run docker to start the bot. 
You should define two environment variables: 
- ENV that's the symfony env (prod/dev/test) 
- SECRET that's your symfony secret.

Please note that you MUST host this over https and set a webhook.

### Webhook
To set a webhook use this:

> curl -F "url=https://%domain%/%apikey%" https://api.telegram.org/bot%apikey%/setWebhook

Bash example (untested):
```bash
echo "Enter your api token (urlencoded : => %3A)"
read BOT_API_TOKEN
echo "Enter your domain name"
read DOMAIN
curl -v -H "Content-Type: application/json" -X POST -d '{"url":"https://$(echo $DOMAIN)/$(echo $BOT_API_TOKEN)"}' https://api.telegram.org/bot$(echo $BOT_API_TOKEN)/setwebhook
```

## Licence
MIT


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
You should define an environment variable with your bot API token `BOT_API_TOKEN`.
And you should host this over https, and set a webhook.

### Webhook
To set a webhook use this:

> curl -v -H "Content-Type: application/json" -X POST -d '{"url":"https://bot-domain"}' https://api.telegram.org/bot$(echo $BOT_API_TOKEN)/setwebhook


## Licence
MIT


How to get set up developing on CBTS:

- clone this repo
- set up mysql, php and node
- copy `credentials.php.example` as `credentials.php`.
- create a database - see dbconfig for details. put the mysql password in `credentials.php`
- ensure the path to `node` in `credentials.php` is correct
- if you want the Post! button to work, clone [traceryhosting-send_status](https://github.com/BooDoo/traceryhosting-send_status), run `npm update` to fetch the dependencies, and update the path to `send_status.js` to `credentials.php`
- you can spin up a dev server with the builtin php server like so: `php -S localhost:8000`
- now go check out [traceryhosting-backend](https://github.com/BooDoo/traceryhosting-backend)!
  
  
If you can't tell by the fork info, this is all heavily based on [v21/traceryhosting-frontend](https://github.com/v21/traceryhosting-frontend)  

# TODO:  
  - [X] Support disparate Mastodon instances
  - [X] Remove references to "tweet"s
  - [X] Change validation maximum characters to 500
  - [X] Fix reply grammars not generating
  - [ ] Count raw characters and ditch `twitter-text`?
  - [X] Add {cut …} syntax and display status accordingly
  - [X] Add {alt …} syntax and assign alt/title attributes accordingly
  - [X] Reveal/finish implementing `is_sensitive` flag (...with per status override?)
  - [ ] Page suggesting charitable orgs in lieu of financial support
  - [ ] Add beeping.town server recommendation (after confirming Pleroma is OK)
  - [ ] Get even more specific with callback URI so test instance port is taken into account
  - [ ] Branch/submit PR to [MastodonOAuthPHP](https://github.com/TheCodingCompany/MastodonOAuthPHP) with patches
  - [X] don't hardcode values in the `MastodonOAuthPHP/theCodingCompany/oAuth.php` source
  - [ ] translation/internationalization layer?

# SymfonyGPT

This project is a simple chat which uses the GPT 3.5 Turbo LLM via the OpenAI API. It is mainly used to gain experience with the Open API and the PHP SDK __@orhanerday/open-ai__. Additionally, the chat can be set to specific roles via the env variables. By default, the chat has the role of a Symfony Developer.

## Setup

#### Run composer
```sh
symfony composer install
```

#### Provide your `OPENAI_APIKEY` via the *.env.local*
```sh
touch .env.local
```

#### Build frontend
```sh
yarn install
yarn build
```

#### Add domain (optional)
```sh
symfony proxy:start
symfony proxy:domain:attach symfonygpt
```

#### Run the app
```sh
symfony server:start -d
```

## Prompting

You can also fine tune the chat yourself and therefore assign a new role. Just copy the variables `CHAT_NAME`,  `CHAT_SYSTEM_PROMPT` and  `CHAT_USER_SUFFIX_PROMPT` from the *.env* into your *.env.local* and adjust them as desired. Please note that the  `CHAT_USER_SUFFIX_PROMPT` is appended to every user message and consumes accordingly many tokens.


# Jira Cycle Time

![example workflow](https://github.com/benfreke/jira-cycletime/actions/workflows/actions.yml/badge.svg)
[![codecov](https://codecov.io/gh/benfreke/jira-cycletime/branch/main/graph/badge.svg?token=A5EMTMUVXW)](https://codecov.io/gh/benfreke/jira-cycletime)

Use this Laravel PHP based repository to understand data stored within Jira.
This is based on status categories within Jira.

## How is the data stored

Data for all Jira tickets is stored locally in a postgres database, to enable faster evaluations.

## How is data collected

Data is requested from Jira once an hour.

## Services required

1. This PHP app
2. A database compatible with Eloquent
3. A queue for background jobs (SQS, Redis, Database)

# Data logic

## Models

### Users

#### Relations

- User have many Issues
- User have many Cycletimes

#### Attributes

- Timezone

### Cycletime

Cycletime has a User
Cycletime has a Issue

- Month (datetime that is cast in the model)
- Cycletime

### Transitions

Transition have a Issue

- Type
- Date

### Issues

#### Relations
Issue have a User
Issue have a Cycletime
Issue have many Transition

#### Attributes

## Calculations

### Cycletime

Get every `Issue` that doesn't have a `Cycletime`, or has a newer `Transition` than the last calculated `Cycletime`.

Find the `Transition` of the `Issue` into a `In Progress` state.

Find the latest `Transition` of the `Issue` into a `Done` state.

If both dates are not found, set the time of `Cycletime` to `null`.

For the `User` attached to the `Issue`.
Use that `User` timezone and days of work to calculate the business days.
Save that value to `Cycletime`.
Save the last done `Transition` date into the `Cycletime` model

## Testing locally

Because of the OAuth setup, you will need to set up ngrok and then change the various value to it.

1. Change the `node` service command to be `npm run build`.
2. Change the virtual host of the `nginx` service to be the new ngrok URL
HMR isn't working over ngrok and I can't be bothered figuring out how to get it to work
3. In `.env`, change the `APP_URL` and `ASSET_URL` to the new https URL provided by Ngrok
4. In `routes/web`, add the middleware back in to the route
5. Run `docker compose run -it node` to build the assets

### Ngrok

- You'll need to sign up and get an Authtoken.
- Add that to your .env file (TBD).
- Run the `make ngrok` command (don't do this yet as I've not set it up)

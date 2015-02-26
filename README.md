# Friendica directory, in ReactPHP

The heavy-lifting part of the Friendica directory is about I/O.
Making this non-blocking with React will (hopefully) improve it's performance hugely.
As well as make the code much less complicated.

## Currently supports

* Non-blocking job queue.
* Prioritization of jobs.
* Throttling settings for job concurrency.

## Would like to support

* Non-blocking database connection.
* Throttling settings for network requests.
* Jobs that can be run:
    - Update profile
        - Scrape profile
            - Parse HTML data
        - Noscrape profile
        - Fetch profile image
            - Resize profile image
        - Store profile in DB
    - Probe site
        - Run probe
        - Store information in DB
    - Sync push
        - Fetch push targets
        - Fetch push queue
        - Push to target
    - Sync pull
        - Fetch pull targets
        - Pull updates
        - Add (unique) profile scrapes to job queue
* Accept submit/push over IPC and add to job queue.


### Jobs

A collection of steps.
Has a priority.

### Steps

One normally blocking operation, made non-blocking.

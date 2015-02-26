<?php namespace Friendica\Directory\Job;

/**
 * A collection of priority values.
 * Higher value means higher priority.
 */
abstract class JobPriorities
{
    //The least of our worries is keeping things updated.
    //It should happen but we're not in a hurry.
    const MAINTENANCE   = 10;

    //Checking for health is slightly more important than updating profiles.
    //This is because it will improve consistancy of the statistics.
    const HEALTH        = 20;

    //Sync pulls are less important than pushing, because we want to propagate new information fast.
    const SYNC_PULL     = 50;
    const SYNC_PUSH     = 60;

    //A submit is something a user has just done, we want feedback ASAP.
    const SUBMIT        = 90;
}

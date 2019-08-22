#!/usr/bin/env bash

GIT_BRANCH="$1"
GIT_USER="$2"
GIT_ACCESS_TOKEN="$3"

if [ -z "$GIT_USER" ]
then
    echo "No Git user specified"
    exit 1
fi

if [ -z "$GIT_ACCESS_TOKEN" ]
then
    echo "No Git access token specified"
    exit 1
fi

REPOS=(Api Configuration Console Exceptions Middleware Net RouteAnnotations Router Serialization)

function split()
{
    prefix=$1
    remote=$2
    sha=$(./bin/splitsh-lite --prefix="$prefix")

    if [ -z "$sha" ]
    then
        echo "Empty SHA"
        exit 1
    fi

    echo "$remote"

    # Push to the subtree's repo, and do not leak any sensitive info in the logs
    git push "$remote" "$sha:refs/heads/$GIT_BRANCH" -f >/dev/null 2>&1
}

# Testing devops scripts
git remote add temp https://$GIT_USER:$GIT_ACCESS_TOKEN@github.com:aphiria/temp.git >/dev/null 2>&1
split "src/Api" "temp"

#for repo in ${REPOS[@]}
#do
#    lower_repo=$(echo "$repo" | awk '{print tolower($0)}')
#    git ls-remote --exit-code "$lower_repo"
#
#    if test $? = 1;
#    then
#        # Add the subtree remote, and do not leak any sensitive info in the logs
#        git remote add "$lower_repo" https://$GIT_USER:$GIT_ACCESS_TOKEN@github.com:aphiria/$lower_repo.git >/dev/null 2>&1
#    fi
#
#    split "src/$repo" "$lower_repo"
#done

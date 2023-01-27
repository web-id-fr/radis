#!/bin/bash

BOLD=$(tput bold)
NORMAL=$(tput sgr0)
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

mkdir -p ./_tmp

echo ""
echo "${BOLD}-- Check dead RADIS websites --${NORMAL}"
echo ""
echo "   This script makes a website listing query on Forge server using API,"
echo "   matching a specific github repository. If a website has a branch that"
echo "   does not exists on the repository, it will ask you to delete it."
echo ""

read -p "   Enter github repository name:" remote_repo
read -p "   Enter Forger server ID:" server_id
read -p "   Enter Forger token:" forge_api_token

curl -s -H "Accept: application/json" \
-H "Content-Type: application/json" \
-H "Authorization: Bearer $forge_api_token" \
-X GET https://forge.laravel.com/api/v1/servers/$server_id/sites | jq > ./_tmp/sites.json

echo ""
echo "${BOLD}-- Checking dead RADIS websites --${NORMAL}"
echo ""
echo "   server ID: $server_id"
echo "   repository: $remote_repo"
echo ""
sites_count=$(cat ./_tmp/sites.json | jq -r ".sites | length")
echo "   ${BOLD}$sites_count sites${NORMAL} found"
echo ""

remote_git_repository_name=$(git )
remote_branches=$(git ls-remote --heads git@github.com:$remote_repo.git)

for i in $( seq 0 $((sites_count-1)) )
do
  site_id=$(cat ./_tmp/sites.json | jq -r ".sites[$i].id")
  site_name=$(cat ./_tmp/sites.json | jq -r ".sites[$i].name")
  site_repository=$(cat ./_tmp/sites.json | jq -r ".sites[$i].repository")
  site_branch=$(cat ./_tmp/sites.json | jq -r ".sites[$i].repository_branch")

  # skip if it does not match repository name
    if [[ $site_repository != $remote_repo ]]; then
      printf "⏩️ ${GREEN}$site_name: does not match $remote_repo${NC}\n"
      continue
    fi

  # skip null branch
  if [[ $site_branch == 'null' ]]; then
    printf "⏩️ ${GREEN}$site_name: branch $site_branch${NC}\n"
    continue
  fi

  # skip preprod branch
  if [[ $site_branch == 'preprod' ]]; then
    printf "⏩️ ${GREEN}$site_name: branch $site_branch${NC}\n"
    continue
  fi

  # check if branch exists remotely
  output=$(echo $remote_branches | grep $site_branch)
  if [ -n "$output" ]; then
    printf "✅️ ${GREEN}$site_name: branch $site_branch${NC}\n"
  else
    printf "❌️ ${RED}$site_name: branch $site_branch does not exist!${NC}\n"
    read -p "⚠️ Do you want to delete this website from Forge ? (yes/no)[no]:" confirm
    confirm=${confirm:-"no"}
    if [[ $confirm == 'yes' ]]; then
      printf "⚠️ ${RED}DELETING SITE $site_name [ID $site_id]${NC}\n"
      echo "⚠️ ${BOLD}Removing web site ID $site_id and dependencies${NORMAL}"
      curl -s -H "Accept: application/json" \
        -H "Content-Type: application/json" \
        -H "Authorization: Bearer $forge_api_token" \
        -X DELETE https://forge.laravel.com/api/v1/servers/$server_id/sites/$site_id
    fi
  fi
done

rm -Rf ./_tmp
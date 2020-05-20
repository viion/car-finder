#!/usr/bin/env bash

openssl req -x509 -out localhost.crt -keyout localhost.key \
  -newkey rsa:2048 -nodes -sha256 \
  -subj '/CN=localhost' -extensions EXT -config <( \
   printf "[dn]\nCN=localhost\n[req]\ndistinguished_name = dn\n[EXT]\nsubjectAltName=DNS:localhost\nkeyUsage=digitalSignature\nextendedKeyUsage=serverAuth")

#
# Add to nginx like so:
#
#   listen 80;
#   listen 443 default_server ssl;
#   ssl on;
#   ssl_certificate /home/vagrant/localhost.crt;
#   ssl_certificate_key /home/vagrant/localhost.key;
#

FROM djoq/docker-node
RUN npm install pm2 -g
#RUN npm install coffee-script -g
RUN npm install http-server -g
RUN pm2 install pm2-webshell

RUN mkdir -p /app

RUN apt-get update

ARG VERSION=0.0.1
COPY . /app
EXPOSE 7890
RUN chmod +x /app/run.sh
CMD ["/app/run.sh"]

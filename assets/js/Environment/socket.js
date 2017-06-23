import $ from 'jquery';

class Socket {
  constructor() {
    this.connection = new WebSocket('ws://capistranoui.docker:4200');
    this.logger = $('#socket-content');

    this.connection.onopen = e => {
      this.subscribe();
      console.info("Connection established succesfully");
    };

    this.connection.onmessage = e => {
      this.appendMessage(e.data);

      console.log(e.data);
    };

    this.connection.onerror = function(e){
      alert("Error: something went wrong with the socket.");
      console.error(e);
    };
  }

  appendMessage(message) {
    this.logger.html(this.logger.html() + message);
  }

  flushMessageHistory() {
    this.logger.html('');
  }

  subscribe() {
    const message = {
      action: 'subscribe',
      envId: environmentId,
    };
    this.connection.send(JSON.stringify(message));
  }
}

$(document).ready(function () {
  const socket = new Socket();
});

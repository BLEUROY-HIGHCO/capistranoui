import $ from 'jquery';

class Socket {
  constructor() {
    this.connection = new WebSocket(`ws://${socketPath}`);
    this.logger = $('#socket-content');

    this.connection.onopen = e => {
      this.subscribe();
      console.info("Connection established succesfully.");
    };

    this.connection.onmessage = e => {
      this.appendMessage(e.data);
    };

    this.connection.onerror = e => {
      this.appendMessage("<div>An error occurred while connecting to socket.</div>");
      console.error(e);
    };
  }

  appendMessage(message) {
    this.logger.html(this.logger.html() + message);
    this.logger.scrollTop(this.logger.prop('scrollHeight'));
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

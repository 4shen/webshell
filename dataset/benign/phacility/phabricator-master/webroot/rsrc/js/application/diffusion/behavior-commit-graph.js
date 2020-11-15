/**
 * @provides javelin-behavior-diffusion-commit-graph
 * @requires javelin-behavior
 *           javelin-dom
 *           javelin-stratcom
 */

JX.behavior('diffusion-commit-graph', function(config) {

  var nodes = JX.DOM.scry(document.body, 'div', 'commit-graph');
  var cxt;

  // Pick the color for column 'c'.
  function color(c) {
    var colors = [
      '#cc0000',
      '#cc0099',
      '#6600cc',
      '#0033cc',
      '#00cccc',
      '#00cc33',
      '#66cc00',
      '#cc9900'
    ];
    return colors[c % colors.length];
  }

  // Stroke a line (for lines between commits).
  function lstroke(c) {
    cxt.lineWidth = 3;
    cxt.strokeStyle = '#ffffff';
    cxt.stroke();
    cxt.lineWidth = 1;
    cxt.strokeStyle = color(c);
    cxt.stroke();
  }

  // Stroke with fill (for commit circles).
  function fstroke(c) {
    cxt.lineWidth = 1;
    cxt.fillStyle = color(c);
    cxt.strokeStyle = '#ffffff';
    cxt.fill();
    cxt.stroke();
  }

  // If the graph is going to be wide, squish it a bit so it doesn't take up
  // quite as much space.
  var default_width;
  if (config.count >= 8) {
    default_width = 6;
  } else {
    default_width = 12;
  }

  for (var ii = 0; ii < nodes.length; ii++) {
    var data = JX.Stratcom.getData(nodes[ii]);

    var cell = default_width;
    var xpos = function(col) {
      return (col * cell) + (cell / 2);
    };

    var h = 34;
    var w = cell * config.count;

    var canvas = JX.$N('canvas', {width: w, height: h});
    cxt = canvas.getContext('2d');

    cxt.lineWidth = 3;
    // This gives us sharper lines, since lines drawn on an integer (like 5)
    // are drawn from 4.5 to 5.5.
    cxt.translate(0.5, 0.5);

    cxt.strokeStyle = '#ffffff';
    cxt.fillStyle = '#ffffff';

    // First, figure out which column this commit appears in. It is marked by
    // "o" (if it has a commit after it) or "^" (if no other commit has it as
    // a parent). We use this to figure out where to draw the join/split lines.

    var origin = null;
    var jj;
    var x;
    var c;
    for (jj = 0; jj < data.line.length; jj++) {
      c = data.line.charAt(jj);
      switch (c) {
        case 'o':
        case 'x':
        case '^':
          origin = xpos(jj);
          break;
      }
    }

    // Draw all the join lines. These start at some column at the top of the
    // canvas and join the commit's column. They indicate branching.

    for (jj = 0; jj < data.join.length; jj++) {
      var join = data.join[jj];
      x = xpos(join);

      cxt.beginPath();
        cxt.moveTo(x, 0);
        cxt.bezierCurveTo(x, h/4, origin, h/4, origin, h/2);
      lstroke(join);
    }

    // Draw all the split lines. These start at the commit and end at some
    // column on the bottom of the canvas. They indicate merging.

    for (jj = 0; jj < data.split.length; jj++) {
      var split = data.split[jj];
      x = xpos(split);
      cxt.beginPath();
        cxt.moveTo(origin, h/2);
        cxt.bezierCurveTo(origin, 3*h/4, x, 3*h/4, x, h);
      lstroke(split);
    }

    // Draw the vertical lines (a branch with no activity at this commit) and
    // the commit circles.

    for (jj = 0; jj < data.line.length; jj++) {
      c = data.line.charAt(jj);
      switch (c) {
        case 'o':
        case '^':
        case '|':
        case 'x':
        case 'X':

          if (c !== 'X') {
            cxt.beginPath();
            cxt.moveTo(xpos(jj), (c == '^' ? h/2 : 0));
            cxt.lineTo(xpos(jj), (c == 'x' ? h/2 : h));
            lstroke(jj);
          }

          if (c == 'o' || c == '^' || c == 'x' || c == 'X') {
            cxt.beginPath();
            cxt.arc(xpos(jj), h/2, 3, 0, 2 * Math.PI, true);
            fstroke(jj);
          }
          break;
      }
    }

    JX.DOM.setContent(nodes[ii], canvas);
  }


});

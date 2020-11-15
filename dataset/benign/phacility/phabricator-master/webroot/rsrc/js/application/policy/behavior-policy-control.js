/**
 * @provides javelin-behavior-policy-control
 * @requires javelin-behavior
 *           javelin-dom
 *           javelin-util
 *           phuix-dropdown-menu
 *           phuix-action-list-view
 *           phuix-action-view
 *           javelin-workflow
 *           phuix-icon-view
 * @javelin
 */
JX.behavior('policy-control', function(config) {
  var control = JX.$(config.controlID);
  var input = JX.$(config.inputID);
  var value = config.value;

  if (config.disabled) {
    JX.DOM.alterClass(control, 'disabled-control', true);
    JX.DOM.listen(control, 'click', null, function(e) {
      e.kill();
    });
    return;
  }

  var menu = new JX.PHUIXDropdownMenu(control)
    .setWidth(260)
    .setAlign('left');

  menu.listen('open', function() {
    var list = new JX.PHUIXActionListView();

    for (var ii = 0; ii < config.groups.length; ii++) {
      var group = config.groups[ii];

      list.addItem(
        new JX.PHUIXActionView()
          .setName(config.labels[group])
          .setLabel(true));

      for (var jj = 0; jj < config.order[group].length; jj++) {
        var phid = config.order[group][jj];

        var onselect;
        if (group == 'custom') {
          onselect = JX.bind(null, function(phid) {
            var uri = get_custom_uri(phid, config.capability);

            new JX.Workflow(uri)
              .setHandler(function(response) {
                if (!response.phid) {
                  return;
                }

                replace_policy(phid, response.phid, response.info);
                select_policy(response.phid);
              })
              .start();

          }, phid);
        } else if (phid == config.projectKey) {
          onselect = JX.bind(null, function(phid) {
            var uri = get_custom_uri(phid, config.capability);

            new JX.Workflow(uri)
              .setHandler(function(response) {
                if (!response.phid) {
                  return;
                }

                add_policy(phid, response.phid, response.info);
                select_policy(response.phid);
              })
              .start();
          }, phid);
        } else {
          onselect = JX.bind(null, select_policy, phid);
        }

        var option = config.options[phid];
        var item = new JX.PHUIXActionView()
          .setName(option.name)
          .setIcon(option.icon + ' darkgreytext')
          .setHandler(JX.bind(null, function(fn, e) {
            e.prevent();
            menu.close();
            fn();
          }, onselect));

        if (phid == value) {
          item.setSelected(true);
        }

        list.addItem(item);
      }
    }

    menu.setContent(list.getNode());
  });


  var select_policy = function(phid) {
    JX.DOM.setContent(
      JX.DOM.find(control, 'span', 'policy-label'),
      render_option(phid));

    input.value = phid;
    value = phid;
  };


  var render_option = function(phid, with_title) {
    var option = config.options[phid];

    var name = option.name;
    if (with_title && (option.full != option.name)) {
      name = JX.$N('span', {title: option.full}, name);
    }

    return [render_icon(option.icon), name];
  };

  var render_icon = function(icon) {
    return new JX.PHUIXIconView()
      .setIcon(icon)
      .getNode();
  };

  /**
   * Get the workflow URI to create or edit a policy with a given PHID.
   */
  var get_custom_uri = function(phid, capability) {
    return JX.$U(config.editURI + phid + '/')
      .setQueryParam('capability', capability)
      .toString();
  };


  /**
   * Replace an existing policy option with a new one. Used to swap out custom
   * policies after the user edits them.
   */
  var replace_policy = function(old_phid, new_phid, info) {
    return add_policy(old_phid, new_phid, info, true);
  };


  /**
   * Add a new policy above an existing one, optionally replacing it.
   */
  var add_policy = function(old_phid, new_phid, info, replace) {
    if (config.options[new_phid]) {
      return;
    }

    config.options[new_phid] = info;

    for (var k in config.order) {
      for (var ii = 0; ii < config.order[k].length; ii++) {
        if (config.order[k][ii] == old_phid) {
          config.order[k].splice(ii, (replace ? 1 : 0), new_phid);
          return;
        }
      }
    }
  };

});

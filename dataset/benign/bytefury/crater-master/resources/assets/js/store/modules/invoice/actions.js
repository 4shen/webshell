import * as types from './mutation-types'
import * as dashboardTypes from '../dashboard/mutation-types'

export const fetchInvoices = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/invoices`, {params}).then((response) => {
      commit(types.SET_INVOICES, response.data.invoices.data)
      commit(types.SET_TOTAL_INVOICES, response.data.invoiceTotalCount)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchCreateInvoice = ({ commit, dispatch, state }) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/invoices/create`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchInvoice = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/invoices/${id}/edit`).then((response) => {
      commit(types.SET_TEMPLATE_ID, response.data.invoice.invoice_template_id)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchViewInvoice = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/invoices/${id}`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const sendEmail = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/invoices/send`, data).then((response) => {
      if (response.data.success) {
        commit(types.UPDATE_INVOICE_STATUS, {id: data.id, status: 'SENT'})
        commit('dashboard/' + dashboardTypes.UPDATE_INVOICE_STATUS, {id: data.id, status: 'SENT'}, { root: true })
      }
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

// export const SentEmail = ({ commit, dispatch, state }, invoiceId) => {
//   return new Promise((resolve, reject) => {
//     window.axios.post(`/api/invoices/sent/${invoiceId}`).then((response) => {
//       resolve(response)
//     }).catch((err) => {
//       reject(err)
//     })
//   })
// }

export const addInvoice = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post('/api/invoices', data).then((response) => {
      commit(types.ADD_INVOICE, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteInvoice = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.delete(`/api/invoices/${id}`).then((response) => {
      if (response.data.error) {
        resolve(response)
      } else {
        commit(types.DELETE_INVOICE, id)
        commit('dashboard/' + dashboardTypes.DELETE_INVOICE, id, { root: true })
        resolve(response)
      }
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteMultipleInvoices = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/invoices/delete`, {'id': state.selectedInvoices}).then((response) => {
      if (response.data.error) {
        resolve(response)
      } else {
        commit(types.DELETE_MULTIPLE_INVOICES, state.selectedInvoices)
        resolve(response)
      }
    }).catch((err) => {
      reject(err)
    })
  })
}

export const updateInvoice = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.put(`/api/invoices/${data.id}`, data).then((response) => {
      if (response.data.invoice) {
        commit(types.UPDATE_INVOICE, response.data)
      }
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const markAsSent = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/invoices/mark-as-sent`, data).then((response) => {
      commit(types.UPDATE_INVOICE_STATUS, {id: data.id, status: 'SENT'})
      commit('dashboard/' + dashboardTypes.UPDATE_INVOICE_STATUS, {id: data.id, status: 'SENT'}, { root: true })
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const cloneInvoice = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/invoices/clone`, data).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const searchInvoice = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/invoices?${data}`).then((response) => {
      // commit(types.UPDATE_INVOICE, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const selectInvoice = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECTED_INVOICES, data)
  if (state.selectedInvoices.length === state.invoices.length) {
    commit(types.SET_SELECT_ALL_STATE, true)
  } else {
    commit(types.SET_SELECT_ALL_STATE, false)
  }
}

export const setSelectAllState = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE, data)
}

export const selectAllInvoices = ({ commit, dispatch, state }) => {
  if (state.selectedInvoices.length === state.invoices.length) {
    commit(types.SET_SELECTED_INVOICES, [])
    commit(types.SET_SELECT_ALL_STATE, false)
  } else {
    let allInvoiceIds = state.invoices.map(inv => inv.id)
    commit(types.SET_SELECTED_INVOICES, allInvoiceIds)
    commit(types.SET_SELECT_ALL_STATE, true)
  }
}

export const resetSelectedInvoices = ({ commit, dispatch, state }) => {
  commit(types.RESET_SELECTED_INVOICES)
}
export const setCustomer = ({ commit, dispatch, state }, data) => {
  commit(types.RESET_CUSTOMER)
  commit(types.SET_CUSTOMER, data)
}

export const resetCustomer = ({ commit, dispatch, state }) => {
  commit(types.RESET_CUSTOMER)
}

export const setTemplate = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    commit(types.SET_TEMPLATE_ID, data)
    resolve({})
  })
}

export const selectCustomer = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/customers/${id}`).then((response) => {
      commit(types.RESET_SELECTED_CUSTOMER)
      commit(types.SELECT_CUSTOMER, response.data.customer)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const resetSelectedCustomer = ({ commit, dispatch, state }, data) => {
  commit(types.RESET_SELECTED_CUSTOMER)
}

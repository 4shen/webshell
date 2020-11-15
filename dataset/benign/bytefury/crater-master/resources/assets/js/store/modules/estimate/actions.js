import * as types from './mutation-types'
import * as dashboardTypes from '../dashboard/mutation-types'

export const fetchEstimates = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/estimates`, {params}).then((response) => {
      commit(types.SET_ESTIMATES, response.data.estimates.data)
      commit(types.SET_TOTAL_ESTIMATES, response.data.estimateTotalCount)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const getRecord = ({ commit, dispatch, state }, record) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/estimates/records?record=${record}`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchCreateEstimate = ({ commit, dispatch, state }) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/estimates/create`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchEstimate = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/estimates/${id}/edit`).then((response) => {
      commit(types.SET_TEMPLATE_ID, response.data.estimate.estimate_template_id)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchViewEstimate = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/estimates/${id}`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const sendEmail = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/estimates/send`, data).then((response) => {
      if (response.data.success) {
        commit(types.UPDATE_ESTIMATE_STATUS, {id: data.id, status: 'SENT'})
        commit('dashboard/' + dashboardTypes.UPDATE_ESTIMATE_STATUS, { id: data.id, status: 'SENT' }, { root: true })
      }
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const addEstimate = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post('/api/estimates', data).then((response) => {
      commit(types.ADD_ESTIMATE, response.data.estimate)

      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteEstimate = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.delete(`/api/estimates/${id}`).then((response) => {
      commit(types.DELETE_ESTIMATE, id)
      commit('dashboard/' + dashboardTypes.DELETE_ESTIMATE, id, { root: true })
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteMultipleEstimates = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/estimates/delete`, {'id': state.selectedEstimates}).then((response) => {
      commit(types.DELETE_MULTIPLE_ESTIMATES, state.selectedEstimates)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const updateEstimate = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.put(`/api/estimates/${data.id}`, data).then((response) => {
      commit(types.UPDATE_ESTIMATE, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const markAsAccepted = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/estimates/accept`, data).then((response) => {
      commit('dashboard/' + dashboardTypes.UPDATE_ESTIMATE_STATUS, { id: data.id, status: 'ACCEPTED' }, { root: true })
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const markAsRejected = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/estimates/reject`, data).then((response) => {
      commit('dashboard/' + dashboardTypes.UPDATE_ESTIMATE_STATUS, { id: data.id, status: 'REJECTED' }, { root: true })
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const markAsSent = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/estimates/mark-as-sent`, data).then((response) => {
      commit(types.UPDATE_ESTIMATE_STATUS, {id: data.id, status: 'SENT'})
      commit('dashboard/' + dashboardTypes.UPDATE_ESTIMATE_STATUS, { id: data.id, status: 'SENT' }, { root: true })
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const convertToInvoice = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/estimates/${id}/convert-to-invoice`).then((response) => {
      // commit(types.UPDATE_INVOICE, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const searchEstimate = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/estimates?${data}`).then((response) => {
      // commit(types.UPDATE_INVOICE, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const selectEstimate = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECTED_ESTIMATES, data)
  if (state.selectedEstimates.length === state.estimates.length) {
    commit(types.SET_SELECT_ALL_STATE, true)
  } else {
    commit(types.SET_SELECT_ALL_STATE, false)
  }
}

export const setSelectAllState = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE, data)
}

export const selectAllEstimates = ({ commit, dispatch, state }) => {
  if (state.selectedEstimates.length === state.estimates.length) {
    commit(types.SET_SELECTED_ESTIMATES, [])
    commit(types.SET_SELECT_ALL_STATE, false)
  } else {
    let allEstimateIds = state.estimates.map(estimt => estimt.id)
    commit(types.SET_SELECTED_ESTIMATES, allEstimateIds)
    commit(types.SET_SELECT_ALL_STATE, true)
  }
}

export const resetSelectedEstimates = ({ commit, dispatch, state }) => {
  commit(types.RESET_SELECTED_ESTIMATES)
}

export const setCustomer = ({ commit, dispatch, state }, data) => {
  commit(types.RESET_CUSTOMER)
  commit(types.SET_CUSTOMER, data)
}

export const setItem = ({ commit, dispatch, state }, data) => {
  commit(types.RESET_ITEM)
  commit(types.SET_ITEM, data)
}

export const resetCustomer = ({ commit, dispatch, state }) => {
  commit(types.RESET_CUSTOMER)
}

export const resetItem = ({ commit, dispatch, state }) => {
  commit(types.RESET_ITEM)
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

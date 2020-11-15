import * as types from './mutation-types'

export const fetchExpenses = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/expenses`, {params}).then((response) => {
      commit(types.SET_EXPENSES, response.data.expenses.data)
      commit(types.SET_TOTAL_EXPENSES, response.data.expenses.total)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchCreateExpense = ({ commit, dispatch, state }) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/expenses/create`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchExpense = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/expenses/${id}/edit`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const addExpense = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post('/api/expenses', data).then((response) => {
      // commit(types.ADD_EXPENSE, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const updateExpense = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/expenses/${data.id}`, data.editData).then((response) => {
      commit(types.UPDATE_EXPENSE, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteExpense = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.delete(`/api/expenses/${id}`).then((response) => {
      commit(types.DELETE_EXPENSE, id)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteMultipleExpenses = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/expenses/delete`, {'id': state.selectedExpenses}).then((response) => {
      commit(types.DELETE_MULTIPLE_EXPENSES, state.selectedExpenses)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const setSelectAllState = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE, data)
}

export const selectAllExpenses = ({ commit, dispatch, state }) => {
  if (state.selectedExpenses.length === state.expenses.length) {
    commit(types.SET_SELECTED_EXPENSES, [])
    commit(types.SET_SELECT_ALL_STATE, false)
  } else {
    let allExpenseIds = state.expenses.map(cust => cust.id)
    commit(types.SET_SELECTED_EXPENSES, allExpenseIds)
    commit(types.SET_SELECT_ALL_STATE, true)
  }
}

export const selectExpense = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECTED_EXPENSES, data)
  if (state.selectedExpenses.length === state.expenses.length) {
    commit(types.SET_SELECT_ALL_STATE, true)
  } else {
    commit(types.SET_SELECT_ALL_STATE, false)
  }
}

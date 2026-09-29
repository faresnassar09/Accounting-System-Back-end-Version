import apiClient from './axios'

export default {
  /**
   * Fetch hierarchical Chart of Accounts
   */
  async getCharts() {
    const response = await apiClient.get('/v1/accounting/charts')
    return response.data
  },

  /**
   * Fetch flat list of accounts
   */
  async getAccounts() {
    const response = await apiClient.get('/v1/accounting/accounts')
    return response.data
  },

  /**
   * Fetch paginated journal entries
   */
  async getJournalEntries(params = {}) {
    const response = await apiClient.get('/v1/accounting/journal-entries', { params })
    return response.data
  },

  /**
   * Fetch single journal entry by ID
   */
  async getJournalEntry(id) {
    const response = await apiClient.get(`/v1/accounting/journal-entries/${id}`)
    return response.data
  },

  /**
   * Fetch Trial Balance report
   */
  async getTrialBalance(params = {}) {
    const response = await apiClient.get('/v1/accounting/reports/trial-balance', { params })
    return response.data
  },

  /**
   * Fetch Balance Sheet report
   */
  async getBalanceSheet(params = {}) {
    const response = await apiClient.get('/v1/accounting/reports/balance-sheet', { params })
    return response.data
  },

  /**
   * Fetch Income Statement report
   */
  async getIncomeStatement(params = {}) {
    const response = await apiClient.get('/v1/accounting/reports/income-statement', { params })
    return response.data
  },

  /**
   * Fetch Cash Flow Statement report
   */
  async getCashFlow(params = {}) {
    const response = await apiClient.get('/v1/accounting/reports/cash-flow', { params })
    return response.data
  },

  /**
   * Fetch AR Aging report
   */
  async getArAging() {
    const response = await apiClient.get('/v1/accounting/reports/ar-aging')
    return response.data
  },

  /**
   * Fetch AP Aging report
   */
  async getApAging() {
    const response = await apiClient.get('/v1/accounting/reports/ap-aging')
    return response.data
  },
}

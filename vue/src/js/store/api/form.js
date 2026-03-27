import axios from 'axios'
import {
  globalError
} from '@/utils/errors'

const component = 'FORM'

const withCsrfToken = (data = {}) => {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  if (!token) return data

  if (data instanceof FormData) {
    if (!data.has('_token')) {
      data.append('_token', token)
    }
    return data
  }

  if (typeof data === 'object' && data !== null) {
    return {
      ...data,
      _token: data._token || token
    }
  }

  return data
}

export default {
  get (endpoint, callback, errorCallback) {
    axios.get(endpoint).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Get request error.',
        value: resp
      }
      globalError(component, error)
      if (errorCallback && typeof errorCallback === 'function') errorCallback(resp)
    })
  },
  post (endpoint, data, callback, errorCallback) {
    axios.post(endpoint, withCsrfToken(data), {
      validateStatus: status => (status >= 200 && status < 300) || status === 422 || status === 403 || status === 419
    }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }).catch(function (err) {
      const error = {
        message: 'Post request error.',
        value: err
      }

      globalError(component, error)

      if (errorCallback && typeof errorCallback === 'function') errorCallback(err.response)
    })
  },
  put (endpoint, data, callback, errorCallback) {
    axios.put(endpoint, withCsrfToken(data), {
      validateStatus: status => (status >= 200 && status < 300) || status === 422 || status === 403 || status === 419
    }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }).catch(function (err) {
      const error = {
        message: 'Put request error.',
        value: err
      }
      globalError(component, error)

      if (errorCallback && typeof errorCallback === 'function') errorCallback(err.response)
    })
  }
}

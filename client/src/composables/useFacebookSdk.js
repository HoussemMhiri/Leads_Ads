export function useFacebookSdk(appId) {
  const loadFacebookSdk = () => {
    return new Promise((resolve, reject) => {
      // If already loaded, resolve immediately
      if (window.FB) {
        resolve(window.FB)
        return
      }

      // Init logic will run when SDK is ready
      window.fbAsyncInit = function () {
        try {
          FB.init({
            appId: appId,
            cookie: true,
            xfbml: true,
            version: 'v24.0',
          })
          resolve(FB)
        } catch (err) {
          reject(err)
        }
      }

      // Load SDK if not already present
      if (!document.getElementById('facebook-jssdk')) {
        const script = document.createElement('script')
        script.id = 'facebook-jssdk'
        script.src = 'https://connect.facebook.net/en_US/sdk.js'
        script.async = true
        script.defer = true
        script.onerror = () => reject(new Error('Failed to load Facebook SDK'))
        document.body.appendChild(script)
      }
    })
  }

  return {
    loadFacebookSdk,
  }
}

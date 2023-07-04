import { useState, useEffect } from "react";
import type { NextPage } from "next";
import { useWallet } from '@meshsdk/react';
import { CardanoWallet } from '@meshsdk/react';
import Router from 'next/router';
import Cookies from 'js-cookie';

const Home: NextPage = () => {
  const { connected, wallet } = useWallet();
  const [assetExists, setAssetExists] = useState<null | boolean>(null);
  const token = getURLParameter('token');

  function getURLParameter(name) {
    if (typeof window !== 'undefined') {
      const urlParams = new URLSearchParams(window.location.search);
      return urlParams.get(name);
    }
    return null;
  }
  

  function base64Encode(str) {
    return btoa(str);
  }

  function base64Decode(encodedStr) {
    return atob(encodedStr);
  }

  useEffect(() => {
    async function checkAsset() {
      if (wallet && connected) {
        const assets = await wallet.getAssets();
        const assetExists = assets.find(asset => asset.fingerprint === token) ? true : false;
        setAssetExists(assetExists);

        // Set cookie with wallet contents JSON
        Cookies.set('token', base64Encode(token));
      }
    }
    checkAsset();
  }, [wallet, connected]);

  useEffect(() => {
    // Check for the asset fingerprint in the wallet contents stored in the cookie on page reload
    const walletContentsCookie = Cookies.get('walletContents');
    if (walletContentsCookie) {
      const walletContents = JSON.parse(walletContentsCookie);
      const assets = walletContents.assets || [];
      const assetExists = assets.find(asset => asset.fingerprint === token) ? true : false;
      setAssetExists(assetExists);

      // Auto-load restricted content if the fingerprint exists in the cookie
      if (assetExists) {
        handleRedirect();
      }
    }
  }, []);

  const handleRedirect = () => {
    // Redirect the user to the URL
    Router.push('https://thekreeps.com/?k=' + base64Encode(token)); // Replace with your desired URL
  };

  return (
    <div>
      {!connected && (
        <>
          <h1>Connect Wallet</h1>
          <CardanoWallet />
        </>
      )}
      {connected && assetExists === true && (
        <>
          <button class="unlocked" onClick={handleRedirect}>
            UNLOCKED CONTENT
          </button>
        </>
      )}
      {connected && assetExists === false && (
        <>
          <p class="restricted">
            RESTRICTED CONTENT
          </p>
        </>
      )}
    </div>
  );
};

export default Home;

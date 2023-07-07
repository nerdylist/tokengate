// Based on: https://github.com/MeshJS/mesh
// Updated FE w/ UI and logic for tokengate
// New repo: https://github.com/nerdylist/tokengate
import { useState, useEffect } from "react";
import { useWallet, CardanoWallet } from '@meshsdk/react';
import Cookies from 'js-cookie';

const Home = () => {
  const { connected, wallet } = useWallet();
  const [assetExists, setAssetExists] = useState<null | boolean>(null);
  const [token, setToken] = useState<string | null>(null);
  const [showUnlockedContent, setShowUnlockedContent] = useState<boolean>(false);
  const [showRestrictedContent, setShowRestrictedContent] = useState<boolean>(false);
  const [showConnectWallet, setShowConnectWallet] = useState<boolean>(true);

  function base64Encode(str: string) {
    return btoa(str);
  }

  useEffect(() => {
    if (typeof window !== 'undefined') {
      setToken(new URLSearchParams(window.location.search).get('token'));
    }
  }, []);

  useEffect(() => {
    async function checkAsset() {
      if (wallet && connected && token) {
        const assets = await wallet.getAssets();
        const assetExists = assets.find(asset => asset.fingerprint === token) ? true : false;
        setAssetExists(assetExists);

        // Set cookie with token
        if (assetExists) {
          Cookies.set('token', base64Encode(token));
          setShowUnlockedContent(true);
          setShowRestrictedContent(false);
          setShowConnectWallet(false);
        } else {
          Cookies.set('token', '0');
          setShowUnlockedContent(false);
          setShowRestrictedContent(true);
          setShowConnectWallet(false);
        }
      }
    }
    checkAsset();
  }, [wallet, connected, token]);

  useEffect(() => {
    const tokenCookie = Cookies.get('token');
    if (tokenCookie) {
      if (tokenCookie !== '0' && tokenCookie !== base64Encode(token)) {
        Cookies.remove('token');
        setShowConnectWallet(true);
      } else {
        setShowUnlockedContent(tokenCookie !== '0');
        setShowRestrictedContent(tokenCookie === '0');
        setShowConnectWallet(false);
      }
    } else {
      setShowConnectWallet(true);
    }
  }, [token]);

  return (
    <div class="tokengate">
      {showConnectWallet && (
        <>
          <CardanoWallet />
        </>
      )}
      {showUnlockedContent && (
        <>
          <p className="unlocked">
            UNLOCKED CONTENT
          </p>
        </>
      )}
      {showRestrictedContent && (
        <>
          <p className="restricted">
            RESTRICTED CONTENT
          </p>
        </>
      )}
    </div>
  );
};

export default Home;

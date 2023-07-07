// Title: TokenGate
// Description: A simple token gate for Cardano NFTs
// Author: NerdyList
// Version: 0.1.0
// Based on: https://github.com/MeshJS/mesh
// Repo: https://github.com/nerdylist/tokengate
import { useState, useEffect } from "react";
import { useWallet, CardanoWallet } from '@meshsdk/react';
import Cookies from 'js-cookie';

const Home = () => {
  const { connected, wallet } = useWallet();
  const [assetExists, setAssetExists] = useState<null | boolean>(null);
  const [token, setToken] = useState<string | null>(null);
  const [policy, setPolicy] = useState<string | null>(null);
  const [showUnlockedContent, setShowUnlockedContent] = useState<boolean>(false);
  const [showRestrictedContent, setShowRestrictedContent] = useState<boolean>(false);
  const [showConnectWallet, setShowConnectWallet] = useState<boolean>(true);

  function base64Encode(str: string) {
    return btoa(str);
  }

  useEffect(() => {
    if (typeof window !== 'undefined') {
      setToken(new URLSearchParams(window.location.search).get('token'));
      setPolicy(new URLSearchParams(window.location.search).get('policy'));
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
    async function checkPolicy() {
      if (wallet && connected && policy) {
        const assets = await wallet.getAssets();
        const policyExists = assets.find(asset => asset.policyId === policy) ? true : false;
        
        // Set cookie with policy
        if (policyExists) {
          Cookies.set('policy', base64Encode(policy));
          setShowUnlockedContent(true);
          setShowRestrictedContent(false);
          setShowConnectWallet(false);
        } else if (!assetExists) { // Only set to '0' if no asset exists
          Cookies.set('policy', '0');
          setShowUnlockedContent(false);
          setShowRestrictedContent(true);
          setShowConnectWallet(false);
        }
      }
    }
    checkPolicy();
  }, [wallet, connected, policy, assetExists]);

  useEffect(() => {
    const tokenCookie = Cookies.get('token');
    const policyCookie = Cookies.get('policy');
    if (tokenCookie || policyCookie) {
      const validToken = tokenCookie !== '0' && tokenCookie === base64Encode(token);
      const validPolicy = policyCookie !== '0' && policyCookie === base64Encode(policy);
      setShowUnlockedContent(validToken || validPolicy);
      setShowRestrictedContent(!validToken && !validPolicy);
      setShowConnectWallet(false);
    } else {
      setShowConnectWallet(true);
    }
  }, [token, policy]);

  return (
    <div className="tokengate">
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

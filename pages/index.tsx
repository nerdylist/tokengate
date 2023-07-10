// Title: TokenGate
// Description: A simple token gate for Cardano NFTs
// Author: NerdyList
// Version: 0.1.0
//
// Resource References:
// https://github.com/MeshJS/mesh
// https://docs.blockfrost.io/
//
// Repo: https://github.com/nerdylist/tokengate
//
// Test Policy: b100d99535d04dc2cd28ffaad90dcc57f01f6dce6b565f4be8fab033
// Test Token: asset1t84stfqy424gh4nsdsfe5gfqzy4lpe8ssm0fjj
// Test Project: mainnetvwyJfkUE0zpNS88YopYlHKo0ku3nV2Zc

import { useState, useEffect } from "react";
import { useWallet, CardanoWallet } from '@meshsdk/react';
import Cookies from 'js-cookie';

const Home = () => {
  const { connected, wallet, disconnect } = useWallet();
  const [assetExists, setAssetExists] = useState<null | boolean>(null);
  const [token, setToken] = useState<string | null>(null);
  const [policy, setPolicy] = useState<string | null>(null);
  const [showUnlockedContent, setShowUnlockedContent] = useState<boolean>(false);
  const [showRestrictedContent, setShowRestrictedContent] = useState<boolean>(false);
  const [showConnectWallet, setShowConnectWallet] = useState<boolean>(true);

  function base64Encode(str: string) {
    return btoa(str);
  }

  function handleHide() {
    Cookies.remove('token');
    Cookies.remove('policy');
    disconnect();
    setShowUnlockedContent(false);
    setShowRestrictedContent(false);
    setShowConnectWallet(true);
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
      if (wallet && connected && policy && !token) { // Only check policy if no token is provided
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
  }, [wallet, connected, policy, assetExists, token]); // Added token to the dependency array

  useEffect(() => {
    const tokenCookie = Cookies.get('token');
    const policyCookie = Cookies.get('policy');
    if (tokenCookie || policyCookie) {
      const validToken = tokenCookie !== '0' && token !== null && tokenCookie === base64Encode(token);
      const validPolicy = policyCookie !== '0' && policy !== null && policyCookie === base64Encode(policy);
      setShowUnlockedContent(validToken || validPolicy);
      setShowRestrictedContent(!validToken && !validPolicy);
      setShowConnectWallet(!(validToken || validPolicy)); // Hide Connect Wallet button if a valid token or policy exists
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
            UNLOCKED CONTENT<br/>
            <a href="#" onClick={handleHide}>HIDE</a>
          </p>
        </>
      )}
      {showRestrictedContent && (
        <>
          <p className="restricted">
            RESTRICTED CONTENT<br/>
            <a href="#" onClick={handleHide}>HIDE</a>
          </p>
        </>
      )}
    </div>
  );
};

export default Home;

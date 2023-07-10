/* Title: TokenGate
  * Description: A simple token gate for Cardano NFTs
  * Author: NerdyList
  * Version: 0.1.7.1
  *
  * Resource References:
  * https://github.com/MeshJS/mesh
  * https://docs.blockfrost.io/
  *
  * Repo: https://github.com/nerdylist/tokengate
  * Main UI: https://raw.githubusercontent.com/nerdylist/tokengate/main/pages/index.tsx
  * Main CSS: https://raw.githubusercontent.com/nerdylist/tokengate/main/styles/globals.css
  *
  * Test Policy: b100d99535d04dc2cd28ffaad90dcc57f01f6dce6b565f4be8fab033
  * Test Token: asset1t84stfqy424gh4nsdsfe5gfqzy4lpe8ssm0fjj
  * Test Project: mainnetvwyJfkUE0zpNS88YopYlHKo0ku3nV2Zc
*/

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
  
  function base64Encode(str: string) {
    return btoa(str);
  }

  function handleHide() {
    Cookies.remove('token');
    Cookies.remove('policy');
    disconnect();
    setShowUnlockedContent(false);
    setShowRestrictedContent(false);
  }

  useEffect(() => {
    if (typeof window !== 'undefined') {
      setToken(new URLSearchParams(window.location.search).get('token'));
      setPolicy(new URLSearchParams(window.location.search).get('policy'));
    }
  }, []);

  useEffect(() => {
    async function checkAsset() {
      if (wallet && connected

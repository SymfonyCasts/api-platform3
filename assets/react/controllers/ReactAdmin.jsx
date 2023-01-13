import { HydraAdmin } from "@api-platform/admin";
import React from 'react';

export default (props) => (
  <HydraAdmin entrypoint={props.entrypoint} />
);

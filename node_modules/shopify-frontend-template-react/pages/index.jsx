import {
    Card,
    Page,
    Layout,
    TextContainer,
    Image,
    Stack,
    Link,
    Heading,
    FormLayout, TextField, Button
    } from "@shopify/polaris";
import { TitleBar, Toast } from "@shopify/app-bridge-react";

import { trophyImage } from "../assets";
import { ProductsCard, UploadLogo } from "../components";
import {useState, useCallback} from 'react';
import { useAppQuery, useAuthenticatedFetch } from "../hooks";

export default function HomePage() {
  const fetch = useAuthenticatedFetch();
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');

  const emptyToastProps = { content: null };
  const [toastProps, setToastProps] = useState(emptyToastProps);
  const toastMarkup = toastProps.content && (
      <Toast {...toastProps} onDismiss={() => setToastProps(emptyToastProps)} />
  );

  const handleExportOrders = async () => {

    var formData = new FormData();
    formData.append('start_date', startDate);
    formData.append('end_date', endDate);

    const rawResponse = await fetch('/api/export_order', {
      method: 'POST',
      body: formData
    });
    const obj = await rawResponse.json();
    if(obj.success=='true'){
      window.open(obj.export_url,'_blank');
    }else{
      setToastProps({
        content: obj.message,
        error: true,
      });
    }

  };

  return (
      <Page narrowWidth title="Shop Subsea 7 - Home">
        <Layout>
          <Layout.Section>
            <Card sectioned title="Export Orders CSV">
              <FormLayout>
                <FormLayout.Group>
                  <TextField type="date" label="Start Date" value={startDate} onChange={(newValue) => setStartDate(newValue)} autoComplete="off" />
                  <TextField type="date" label="End Date"value={endDate} onChange={(newValue) => setEndDate(newValue)} autoComplete="off" />
                </FormLayout.Group>
                <FormLayout.Group>
                  <Button onClick={() => handleExportOrders()}>Export Ordrs</Button>
                </FormLayout.Group>
              </FormLayout>
            </Card>
          </Layout.Section>
          <Layout.Section>
            <UploadLogo />
          </Layout.Section>
        </Layout>
      </Page>
  );
}

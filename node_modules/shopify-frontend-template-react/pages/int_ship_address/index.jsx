import {
    Card,
    Page,
    Layout,
    TextContainer,
    Image,
    Stack,
    Link,
    Heading,
    FormLayout, TextField, Button, DataTable
    } from "@shopify/polaris";
import { TitleBar, Toast, useAppBridge } from "@shopify/app-bridge-react";
import { Redirect } from "@shopify/app-bridge/actions";

import {useState, useCallback} from 'react';

import { useAppQuery, useAuthenticatedFetch } from "../../hooks";

export default function HomePage() {
    const app = useAppBridge();
    const redirect = Redirect.create(app);

    const fetch = useAuthenticatedFetch();
    const [dataListRow, setDataListRow] = useState([]);

    const emptyToastProps = { content: null };
    const [toastProps, setToastProps] = useState(emptyToastProps);
    const toastMarkup = toastProps.content && (
        <Toast {...toastProps} onDismiss={() => setToastProps(emptyToastProps)} />
    );

    const { data: dataGetIntShipList, refetch: refetchGetIntShipList, isLoading: isLoadingGetIntShipList, isRefetching: isRefetchingGetIntShipList } = useAppQuery({
        url: "/api/get_int_ship_address_list",
        reactQueryOptions: {
            onSuccess: () => {
                let row = [];
                if(dataGetIntShipList && dataGetIntShipList.success=='true' && Object.keys(dataGetIntShipList.data).length > 0){
                    Object.keys(dataGetIntShipList.data).forEach(function(val){
                        row.push([
                            dataGetIntShipList.data[val].isa_first_name+' '+dataGetIntShipList.data[val].isa_last_name,

                            dataGetIntShipList.data[val].isa_address_1+' '+dataGetIntShipList.data[val].isa_address_2+' '+dataGetIntShipList.data[val].isa_city+' '+dataGetIntShipList.data[val].isa_state+' '+dataGetIntShipList.data[val].isa_country+' '+dataGetIntShipList.data[val].isa_zipcode,

                            <Button onClick={() => {redirect.dispatch(Redirect.Action.APP, '/int_ship_address/new?id='+dataGetIntShipList.data[val].isa_id)} }>Edit</Button>
                        ]);
                    });
                }
                setDataListRow(row);
            }
        }
    });

    return (
        <Page title="International Shipping Addresses" primaryAction={{
            content:"Add New",
            onAction:() => { redirect.dispatch(Redirect.Action.APP, '/int_ship_address/new'); }
        }}>
            <Layout>
                <Layout.Section>
                    <Card sectioned >
                    {(dataListRow && dataListRow.length>0) ? (
                        <div>
                            <DataTable
                                columnContentTypes={[
                                    'text',
                                    'text',
                                    'text'
                                ]}
                                headings={[
                                    'Name',
                                    'Address',
                                    'Action'
                                ]}
                                rows={dataListRow}
                            />
                        </div>
                    ):(
                        <div style={{textAlign:"center"}}>No records found</div>
                    )}
                    </Card>
                </Layout.Section>
            </Layout>
        </Page>
    );
}
